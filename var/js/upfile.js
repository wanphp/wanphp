$(document).ready(function () {
  $.extend({
    uploadFile: function (options) {
      if (!options.maxSize) options.maxSize = 100;
      if (!options.success) options.success = function (data) {
        console.log(data);
      };
      if (!options.error) options.error = function (data) {
        console.log(data);
      };
      if (!options.uploadStart) options.uploadStart = function (data) {
        console.log(data);
      };
      if (!options.processResults) options.processResults = function (data) {
        console.log(data);
      };
      if (!options.file) {
        $('#form-file-upload').remove();
        $('body').prepend('<form enctype="multipart/form-data" id="form-file-upload" style="display: none;"><input type="file" name="file" value="" accept="' + options.accept + '"></form>');
        $('#form-file-upload input[name=\'file\']').trigger('click').on('change', function (event) {
          options.file = event.currentTarget.files[0];
          handleFileUpload(options)
        });
      } else {
        handleFileUpload(options)
      }

      function handleFileUpload(options) {
        let file;
        if (options.file) {
          options.uploadStart('上传开始');
          file = options.file;

          const ext = file['name'].replace(/^.+\./, '').toLowerCase();
          if (options.ext.indexOf(ext) === -1) {
            options.error({code: 'error', errMsg: '格式不支持，请选择' + options.ext + '格式的文件'});
            return false;
          }
          // 图片大于2M才做压缩,图片压缩,gif有可能是动图，不做压缩
          if (file.size > 2097152 && options.compress && ['jpg', 'jpeg', 'png'].includes(ext)) {
            let reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => {
              let sourceImage = new Image();
              sourceImage.src = reader.result;
              sourceImage.onload = () => {
                const w = sourceImage.naturalWidth;
                const h = sourceImage.naturalHeight;

                if (!options.compress.maxWidth) options.compress.maxWidth = w;
                if (!options.compress.maxHeight) options.compress.maxHeight = h;

                const segmentHeight = options.compress.maxHeight; // 每段的最大高度
                // 插入summernote的才分片，其它正常压缩
                let totalSegments = Math.ceil(h / segmentHeight);
                if (options.compress.summernote === undefined) totalSegments = 1;
                const ratio = Math.min(1, options.compress.maxWidth / w, (totalSegments > 1 ? 1 : segmentHeight / h));

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = w * ratio;


                // 图片分片数据
                let segmentData = [];
                const uploadSegment = (segmentIndex) => {
                  const startHeight = segmentIndex * segmentHeight;
                  const visibleHeight = Math.min(segmentHeight, h - startHeight);
                  canvas.height = visibleHeight * ratio;

                  ctx.clearRect(0, 0, canvas.width, canvas.height);
                  ctx.drawImage(
                    sourceImage,
                    0, startHeight, w, visibleHeight,
                    0, 0, canvas.width, canvas.height
                  );

                  let arr = canvas.toDataURL("image/jpeg", options.compress.quality).split(',');
                  let mime = arr[0].match(/:(.*?);/)[1];
                  let bstr = atob(arr[1]);
                  let n = bstr.length;
                  let u8arr = new Uint8Array(n);
                  while (n--) u8arr[n] = bstr.charCodeAt(n);

                  const form_data = new FormData();
                  form_data.append('file', new Blob([u8arr], {type: mime}), `分片${segmentIndex + 1}_${file.name}`);
                  form_data.append('type', mime);
                  form_data.append('uid', (options.uid ?? 0).toString());
                  form_data.append('id', (options.fileId ?? 0).toString());

                  $.ajax({
                    url: options.url,
                    type: 'post',
                    dataType: 'json',
                    data: form_data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (json) {
                      if (segmentIndex + 1 < totalSegments) {
                        uploadSegment(segmentIndex + 1);
                        segmentData[segmentIndex] = json;
                      } else {
                        sourceImage = null; // 释放资源
                        if (totalSegments > 1) {
                          segmentData[segmentIndex] = json;
                          options.success(segmentData);
                        } else {
                          options.success(json);
                        }
                      }
                    },
                    error: function (data) {
                      options.error(data);
                    }
                  });
                };

                uploadSegment(0);
              };
            };
            return false;
          }

          if (file.size <= 2097152) {
            const form_data = new FormData();
            form_data.append('file', file, file.name);
            form_data.append('type', file.type);
            form_data.append('size', file.size);
            form_data.append('uid', (options.uid ?? 0).toString());
            $.ajax({
                url: options.url,
                type: 'post',
                dataType: 'json',
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (json) {
                  options.success(json);
                },
                error: function (data) {
                  options.error(data);
                }
              }
            );
            return false;
          }
          let fileReader = new FileReader(),
            blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice,
            filesize = (file.size / 1024 / 1024).toFixed(2) + 'M',
            chunkSize = 2097152,//每块2M
            chunks = Math.ceil(file.size / chunkSize),
            currentChunk = 0,
            spark = new SparkMD5.ArrayBuffer(),
            cutFile = function (file) {//文件分割
              console.log(currentChunk);
              const start = currentChunk * chunkSize,
                end = ((start + chunkSize) >= file.size) ? file.size : start + chunkSize;
              return blobSlice.call(file, start, end);
            },
            loadNext = function () {
              fileReader.readAsArrayBuffer(cutFile(file));
            };
          fileReader.onload = function (e) {
            console.log(((currentChunk + 1) / chunks * 100).toFixed(2) + '%');
            options.processResults('读取文件' + (currentChunk / chunks * 100).toFixed(2) + '%');
            spark.append(e.target.result);
            currentChunk++;
            if (currentChunk < chunks) {
              loadNext();
            } else {
              currentChunk = 0;
              options.processResults('开始上传...');
              upload(spark.end());
            }
          };
          fileReader.onerror = function () {
            options.error({code: 'error', errMsg: '文件读取出错'});
          };

          function upload(file_md5) {
            const form_data = new FormData();
            form_data.append('file', cutFile(file), file.name);
            form_data.append('current_chunk', (currentChunk + 1).toString());
            form_data.append('chunks', chunks.toString());
            form_data.append('type', file.type);
            form_data.append('size', file.size);
            form_data.append('md5', file_md5);
            form_data.append('uid', (options.uid ?? 0).toString());
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
                    options.processResults((currentChunk / chunks * 100).toFixed(2) + '%');
                    upload(file_md5);
                  } else {
                    options.processResults('100%');
                    options.success(json);
                  }
                },
                error: function (data) {
                  options.error(data);
                }
              }
            );
          }

          options.processResults('读取文件中...');
          if (chunks > (options.maxSize / 2)) {
            options.error({code: 'error', errMsg: '上传文件不能超过' + options.maxSize + 'M,当前文件大小' + filesize});
            return false;
          }
          loadNext();
          return false;
        }
      }
    }

  });
});
