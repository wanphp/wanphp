$(document).ready(function () {
  $.extend({
    uploadImage: function (url, callback) {
      $('#form-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" value="" accept="image/gif,image/jpeg,image/jpg,image/png"></form>');
      $('#form-upload input[name=\'file\']').trigger('click');

      if (typeof timer != 'undefined') {
        clearInterval(timer);
      }

      timer = setInterval(function () {
        if ($("#form-upload input[name='file']").val() != '') {
          var file = $('#form-upload input[name="file"]')[0].files[0];
          clearInterval(timer);
          var form_data = new FormData();
          var ext = file['name'].replace(/^.+\./, '').toLowerCase();
          console.log(file.type);
          if (ext == 'gif') {
            if(file.size > 2097152){
              var fileReader = new FileReader(),
                blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice,
                filesize = (file.size / 1024 / 1024).toFixed(2) + 'M',
                chunkSize = 2097152,//每块2M
                chunks = Math.ceil(file.size / chunkSize),
                currentChunk = 0,
                spark = new SparkMD5.ArrayBuffer(),
                cutFile = function (file) {//文件分割
                  console.log(currentChunk);
                  var start = currentChunk * chunkSize, end = ((start + chunkSize) >= file.size) ? file.size : start + chunkSize;
                  return blobSlice.call(file, start, end);
                },
                loadNext = function () {
                  fileReader.readAsArrayBuffer(cutFile(file));
                };
              fileReader.onload = function (e) {
                console.log((parseInt(currentChunk + 1) / chunks * 100).toFixed(2) + '%');
                spark.append(e.target.result);
                currentChunk++;
                if (currentChunk < chunks) {
                  loadNext();
                } else {
                  currentChunk = 0;
                  upload(spark.end(), 1);
                }
              };
              fileReader.onerror = function () {
                callback({code: 'error', description: '文件读取出错'});
              };

              function upload(file_md5) {
                var form_data = new FormData();
                form_data.append('file', cutFile(file), file.name);
                form_data.append('current_chunk', currentChunk + 1);
                form_data.append('chunks', chunks);
                form_data.append('type', file.type);
                form_data.append('size', file.size);
                form_data.append('md5', file_md5);
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'json',
                    data: form_data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (json) {
                      if (json.current_chunk && json.current_chunk < chunks) {
                        currentChunk = parseInt(json.current_chunk);
                        console.log(currentChunk, (currentChunk / chunks * 100).toFixed(2) + '%');
                        upload(file_md5);
                      } else {
                        callback(json);
                      }
                    },
                    error: function (data) {
                      callback(data.responseJSON.errMsg);
                    }
                  }
                );
              }
              if (chunks > 50) {
                callback({code: 'error', description: '上传文件不能超过100M,当前文件大小' + filesize});
                return false;
              }
              loadNext();
            }else{
              form_data.append('file', file);
              upload_file(url, form_data, function (res) {
                callback(res);
              });
            }
          } else if (file.size > 1048576) {
            photoCompress(file, {
              width: 1280,
              quality: 0.7
            }, function (base64Codes) {
              var bl = convertBase64UrlToBlob(base64Codes);
              form_data.append("file", bl, file.name);
              upload_file(url, form_data, function (res) {
                callback(res);
              });
            });
          } else {
            form_data.append('file', file);
            upload_file(url, form_data, function (res) {
              callback(res);
            });
          }
        }
      }, 500);
    }
  });

  function upload_file(url, form_data, callback) {
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data: form_data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (json) {
          callback(json);
        },
        error: function (data) {
          callback(data.responseJSON.error);
        }
      }
    );
  }

  function photoCompress(file, w, objDiv) {
    /*图片方向角*/
    var Orientation = null;
    EXIF.getData(file, function () {
      console.log(EXIF.getAllTags(this));
      Orientation = EXIF.getTag(this, 'Orientation');
      console.log(Orientation);
    });
    var ready = new FileReader();
    ready.readAsDataURL(file);
    ready.onload = function () {
      var re = this.result;
      canvasDataURL(re, w, objDiv, Orientation)
    }
  }

  function canvasDataURL(path, obj, callback, Orientation) {
    var img = new Image();
    img.src = path;
    img.onload = function () {
      var that = this;
      var w = that.width,
        h = that.height,
        scale = w / h;
      w = obj.width || w;
      h = obj.height || (w / scale);
      var quality = 0.7;
      var canvas = document.createElement('canvas');
      var ctx = canvas.getContext('2d');
      var anw = document.createAttribute("width");
      anw.nodeValue = w;
      var anh = document.createAttribute("height");
      anh.nodeValue = h;
      canvas.setAttributeNode(anw);
      canvas.setAttributeNode(anh);
      ctx.drawImage(that, 0, 0, w, h);

      /*如果方向角不为1，都需要进行旋转*/
      if (Orientation != "" && Orientation != 1) {
        switch (Orientation) {
          case 6:
            /*需要顺时针（向左）90度旋转*/
            rotateImg(that, 'left', canvas);
            break;
          case 8:
            /*需要逆时针（向右）90度旋转*/
            rotateImg(that, 'right', canvas);
            break;
          case 3:
            /*需要180度旋转*/
            rotateImg(that, 'right', canvas);
            rotateImg(that, 'right', canvas);
            break;
        }
      }

      if (obj.quality && obj.quality <= 1 && obj.quality > 0) {
        quality = obj.quality;
      }
      var base64 = canvas.toDataURL('image/jpeg', quality);
      callback(base64);
    }
  }

  function rotateImg(img, direction, canvas) {
    /*最小与最大旋转方向，图片旋转4次后回到原方向*/
    var min_step = 0;
    var max_step = 3;
    if (img == null) return;
    /*img的高度和宽度不能在img元素隐藏后获取，否则会出错*/
    var height = img.height;
    var width = img.width;
    var step = 2;
    if (step == null) {
      step = min_step;
    }

    if (direction == 'right') {
      step++;
      /*旋转到原位置，即超过最大值*/
      step > max_step && (step = min_step);
    } else {
      step--;
      step < min_step && (step = max_step);
    }

    /*旋转角度以弧度值为参数*/
    var degree = step * 90 * Math.PI / 180;
    var ctx = canvas.getContext('2d');

    switch (step) {
      case 0:
        canvas.width = width;
        canvas.height = height;
        ctx.drawImage(img, 0, 0);
        break;
      case 1:
        canvas.width = height;
        canvas.height = width;
        ctx.rotate(degree);
        ctx.drawImage(img, 0, -height);
        break;
      case 2:
        canvas.width = width;
        canvas.height = height;
        ctx.rotate(degree);
        ctx.drawImage(img, -width, -height);
        break;
      case 3:
        canvas.width = height;
        canvas.height = width;
        ctx.rotate(degree);
        ctx.drawImage(img, -width, 0);
        break;
    }
  }

  function convertBase64UrlToBlob(urlData) {
    var arr = urlData.split(','), mime = arr[0].match(/:(.*?);/)[1],
      bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], {type: mime});
  }
});
