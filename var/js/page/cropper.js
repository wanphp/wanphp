let CROPPER;

function cropper(title, width, height, preview, callback) {
  $('#selectImage').remove();
  $('body').prepend('<form enctype="multipart/form-data" id="selectImage" style="display: none;"><input type="file" name="file" value="" accept="image/gif,image/jpeg,image/jpg,image/png"></form>');
  $('#selectImage input[name=\'file\']').trigger('click').on('change', function (event) {
    let file = event.currentTarget.files[0];
    //读取上传文件
    let reader = new FileReader();
    modalDialog(title, '<div class="cropImageBox"><img id="cropImage" style="max-width: 100%;height: auto"></div>', 'modal-xl', [
      {type: 'primary', value: '1', text: '裁剪图片'},
      {type: 'default', value: '2', text: '使用源图'},
      {type: 'secondary', value: '0', text: '取消'}
    ], function (value) {
      console.log(value)
      if (value === '1') {
        if (CROPPER) {
          CROPPER.getCroppedCanvas({
            width: width, height: height, imageSmoothingQuality: 'high',
          }).toBlob((blob) => {
            const formData = new FormData();
            formData.append('file', blob, 'cropCover.jpg');
            formData.append('uid', currentUser.uid);
            $.ajax({
                url: 'https://images.ztnews.net/upload/thumb',
                type: 'post',
                dataType: 'json',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (res) {
                  if (res.url) callback(res, $(preview));
                  else Toast.fire({icon: 'error', title: res.description});
                }, error: errorDialog
              }
            );
          }, 'image/jpeg');
          CROPPER.destroy();
        }
      } else if (value === '2') {
        let arr = $('#cropImage')[0].src.split(','), mime = arr[0].match(/:(.*?);/)[1],
          blobStr = atob(arr[1]), n = blobStr.length, u8arr = new Uint8Array(n);
        while (n--) u8arr[n] = blobStr.charCodeAt(n);
        const formData = new FormData();
        formData.append('file', new Blob([u8arr], {type: mime}), 'cropCover.jpg');
        formData.append('uid', currentUser.uid);
        $.ajax({
          url: 'https://images.ztnews.net/upload/thumb',
          type: 'post',
          dataType: 'json',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success: function (res) {
            if (res.url) callback(res, $(preview));
            else Toast.fire({icon: 'error', title: res.description});
          }, error: errorDialog
        });
        if (CROPPER) CROPPER.destroy();
      } else {
        CROPPER.destroy();
      }
    }, 'static');
    $('#modalDialog').off('shown.bs.modal').on('shown.bs.modal', function () {
      //readAsDataURL方法可以将File对象转化为data:URL格式的字符串（base64编码）
      reader.readAsDataURL(file);
      reader.onload = (e) => {
        const image = $('#cropImage')[0];
        // 由于 iOS 设备限制内存，当您裁剪大图像（iPhone 相机分辨率）时，浏览器可能会崩溃。为避免这种情况，在开始裁剪之前先调整图像大小为当前能显示的大小
        let sourceImage = new Image();
        sourceImage.onload = () => {
          let canvas = document.createElement('canvas');
          let ctx = canvas.getContext('2d');

          let w = sourceImage.naturalWidth;
          let h = sourceImage.naturalHeight;
          let ratio = Math.min(1, $('.cropImageBox').width() * 2 / w); // 图片大于当前显示的两倍，显示为两倍
          canvas.width = w * ratio;
          canvas.height = h * ratio;

          ctx.drawImage(sourceImage, 0, 0, canvas.width, canvas.height);
          image.src = canvas.toDataURL("image/jpeg", .9);
          // 释放内存占用
          sourceImage = null;
        };
        sourceImage.src = reader.result;

        image.onload = (e) => {
          //console.log(e, image.width, image.height);
          if (CROPPER) CROPPER.destroy();
          //创建cropper实例-----------------------------------------
          CROPPER = new Cropper(image, {
            aspectRatio: width / height,
            minContainerWidth: image.width,   //容器最小的宽度
            minContainerHeight: image.height,
            viewMode: 2,
            dragMode: 'move',
            movable: false,
            zoomable: false,
            preview: [preview]
          });
        }
      }
    });
  });
}
