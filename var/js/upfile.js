$(document).ready(function () {
  let timer;
  $.extend({
    uploadFile: function (options, callback) {
      $('#form-file-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-file-upload" style="display: none;"><input type="file" name="file" value="" accept="' + options.accept + '"></form>');
      $('#form-file-upload input[name=\'file\']').trigger('click');

      if (typeof timer != 'undefined') {
        clearInterval(timer);
      }

      timer = setInterval(function () {
        if ($("#form-file-upload input[name='file']").val() != '') {
          var file = $('#form-file-upload input[name="file"]')[0].files[0];
          clearInterval(timer);
          var ext = file['name'].replace(/^.+\./, '').toLowerCase();
          if (ext != options.ext) {
            callback({code: 'error', description: '格式不支持，请选择' + options.ext + '格式的文件'});
            return false;
          }
          if (file.size <= 2097152) {
            var form_data = new FormData();
            form_data.append('file', file, file.name);
            form_data.append('type', file.type);
            form_data.append('size', file.size);
            $.ajax({
                url: options.url,
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
            return false;
          }
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
              upload(spark.end());
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
                url: options.url,
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
                  callback(data.responseJSON);
                }
              }
            );
          }

          $('#button-upload-file').attr('data-original-title', '读取文件...').tooltip('show');
          if (chunks > 50) {
            callback({code: 'error', description: '上传文件不能超过100M,当前文件大小' + filesize});
            return false;
          }
          loadNext();
          return false;
        }
      }, 500);
    }

  });
});
