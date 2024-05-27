(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof module === 'object' && module.exports) {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(window.jQuery);
  }
}(function ($) {
  // Extends plugins for adding hello.
  //  - plugin is external module for customizing.
  $.extend($.summernote.plugins, {
    imageDialog: function (context) {
      // console.log(context.options)
      this.show = function () {
        $.uploadFile({
          url: context.options.uploadUrl + '/thumb',
          compress: {maxWidth: 1200, maxHeight: 1000, quality: .7},// 编辑器内使用缩略图
          uid: context.options.uid,
          accept: 'image/jpg,image/jpeg,image/png,image/gif',
          ext: '.jpg,.jpeg,.gif,.png',
          success: function (res) {
            if (res.url) context.invoke('editor.insertImage', res.url, '');
            else Toast.fire({icon: 'error', title: res.errMsg});
            Swal.close();
          },
          error: function (res) {
            Toast.fire({icon: 'error', title: res.errMsg});
            Swal.close();
          },
          uploadStart: function (data) {
            console.log(data);
            showLoading(data);
          },
          processResults: function (progress) {
            Swal.update({title: progress});
            Swal.showLoading();
          }
        });
      }
    },
    videoDialog: function (context) {
      this.show = function () {
        $.uploadFile({
          url: context.options.uploadUrl + '/files',
          accept: 'video/mp4',
          ext: 'mp4',
          maxSize: 200,
          uid: context.options.uid,
          success: function (res) {
            if (res.id > 0) {
              const $video = $('<p><video src="' + res.url + '" controls preload="auto" style="margin: 0 auto; max-height: 400px; max-width: 100%"></video><br></p>');
              context.invoke('editor.insertNode', $video[0]);
            } else {
              Toast.fire({icon: 'error', title: res.errMsg});
            }
            Swal.close();
          },
          error: function (res) {
            Swal.close();
            Toast.fire({icon: 'error', title: res.errMsg});
          },
          uploadStart: function (data) {
            console.log(data);
            showLoading(data);
          },
          processResults: function (progress) {
            Swal.update({title: progress});
            Swal.showLoading();
          }
        });
      }
    },
    customPlugin: function (context) {
      const ui = context.ui;
      // 添加按钮
      context.memo('button.cutImageButton', function () {
        return ui.button({
          contents: '<i class="fa fa-cut"></i>',
          tooltip: '剪切图片',
          click: function (e) {
            // 获取选中的图片
            const image = context.invoke('editor.restoreTarget');
            if (image) {
              // 创建一个包含图片的 div 元素
              const imgWrapper = document.createElement('div');
              imgWrapper.appendChild(image.cloneNode(true));
              // 将图片html添加到剪贴板
              navigator.clipboard.write([new ClipboardItem({'text/html': new Blob([imgWrapper.innerHTML], {type: 'text/html'})})]);
              // 删除选中的图片
              context.invoke('editor.removeMedia', image);
            }
          }
        }).render();
      });
      context.memo('button.cropperImageButton', function () {
        return ui.button({
          contents: '<i class="fa fa-crop-alt"></i>',
          tooltip: '裁剪图片',
          click: function (e) {
            // 获取选中的图片
            const image = context.invoke('editor.restoreTarget');
            if (image) {
              //创建cropper实例-----------------------------------------
              new Cropper(image, {
                minContainerWidth: image.width,   //容器最小的宽度
                minContainerHeight: image.height,
                viewMode: 2,
                dragMode: 'move',
                ready() {
                  // 创建popover
                  const _cropper = this.cropper;
                  const $popover = ui.popover({
                    className: 'custom-popover',
                    placement: 'bottom',
                    callback: function ($node) {
                      // 当popover被打开时的回调函数
                      var $content = $node.find('.popover-body,.note-popover-content');
                      $content.prepend('<div class="note-btn-group btn-group"><button type="button" class="note-btn btn btn-light btn-sm confirm-crop">保存</button><button type="button" class="note-btn btn btn-light btn-sm cancel-crop">取消</button></div>');
                      $content.on('click', '.confirm-crop', function (e) {
                        _cropper.getCroppedCanvas().toBlob((blob) => {
                          const formData = new FormData();
                          formData.append('file', blob, 'cropImage.jpg');
                          formData.append('uid', context.options.uid);
                          $.ajax({
                              url: 'https://images.ztnews.net/upload/thumb',
                              type: 'post',
                              dataType: 'json',
                              data: formData,
                              cache: false,
                              contentType: false,
                              processData: false,
                              success: function (json) {
                                $(image).attr('src', json.url);
                                $(image).attr('data-src', json.url);
                              },
                              error: errorDialog
                            }
                          );
                          context.invoke('enable');
                          //context.invoke('editor.insertImage', _cropper.getCroppedCanvas().toDataURL('image/jpeg', 1.0));
                          _cropper.destroy();
                        }, 'image/jpeg');
                      });
                      $content.on('click', '.cancel-crop', function (e) {
                        $popover.remove();
                        _cropper.destroy();
                        context.invoke('enable');
                      });
                    }
                  }).render().appendTo($('.cropper-container')).css({
                    display: 'block', left: e.clientX - context.layoutInfo.editable.offset().left - 50,
                    top: e.clientY - context.layoutInfo.editable.offset().top
                  }).show();
                  context.invoke('disable');
                }
              });
            }
          }
        }).render();
      });
      context.memo('button.imageWatermarkButton', function () {
        return ui.button({
          contents: '<i class="fas fa-expand-arrows-alt"></i>',
          tooltip: '添加水印',
          click: function (e) {
            // 获取选中的图片
            const image = context.invoke('editor.restoreTarget');
            if (image) {
              if (!image.dataset.src) image.dataset.src = $(image).attr('src');
              const $popover = ui.popover({
                className: 'custom-popover',
                placement: 'bottom',
                callback: function ($node) {
                  // 当popover被打开时的回调函数
                  var $content = $node.find('.popover-body,.note-popover-content');
                  $content.prepend('<div class="note-btn-group btn-group-vertical" style="margin-right: 0">' +
                    '<button type="button" class="note-btn btn btn-light btn-sm" data-pos="tl" style="border-top-right-radius: 0;"><i class="fas fa-arrow-left fa-rotate-45"></i></button>' +
                    '<button type="button" class="note-btn btn btn-light btn-sm" data-pos="bl" style="border-bottom-right-radius: 0;"><i class="fas fa-arrow-down fa-rotate-45"></i></button>' +
                    '</div><div class="note-btn-group btn-group-vertical">' +
                    '<button type="button" class="note-btn btn btn-light btn-sm" data-pos="tr" style="border-top-left-radius: 0;border-left: 0;"><i class="fas fa-arrow-up fa-rotate-45"></i></button>' +
                    '<button type="button" class="note-btn btn btn-light btn-sm" data-pos="br" style="border-bottom-left-radius: 0;border-left: 0;"><i class="fas fa-arrow-right fa-rotate-45"></i></button>' +
                    '</div><div class="note-btn-group btn-group-vertical">' +
                    '<button type="button" class="note-btn btn btn-light btn-sm confirm">确定</button>' +
                    '<button type="button" class="note-btn btn btn-light btn-sm cancel">取消</button>' +
                    '</div>');

                  $content.on('click', '.note-btn', function (e) {
                    if ($(this).hasClass('confirm')) {
                      $popover.remove();
                      context.invoke('enable');
                    } else if ($(this).hasClass('cancel')) {
                      $popover.remove();
                      $(image).attr('src', $(image).attr('data-src'));
                      context.invoke('enable');
                    } else {
                      image.src = image.dataset.src.replace('.', '/_ztnews-' + $(this).attr('data-pos') + '.');
                    }
                  });
                }
              }).render().appendTo('.note-editor').css({
                display: 'block',
                left: e.clientX - context.layoutInfo.editable.offset().left - 50,
                top: e.clientY - context.layoutInfo.editable.offset().top
              }).show();
              context.invoke('disable');
            }
          }
        }).render();
      });
      context.memo('button.addImageLink', function () {
        return ui.button({
          contents: '<i class="note-icon-link"></i>',
          tooltip: '添加超链接',
          click: function (e) {
            const image = context.invoke('editor.restoreTarget');
            let imageLink = '';
            if ($(image).parent('a').length) imageLink = $(image).parent('a').attr("href");
            Swal.fire({
              title: '图片超链接',
              input: 'url',
              inputAttributes: {
                autocapitalize: 'off'
              },
              inputValue: imageLink,
              showCancelButton: true,
              confirmButtonText: '确定',
              cancelButtonText: '取消',
              validationMessage: 'URL格式错误'
            }).then((result) => {
              if (result.isConfirmed) {
                if ($(image).parent('a').length) {
                  $(image).parent('a').attr("href", result.value);
                } else {
                  $(image).wrap('<a href="' + result.value + '" target="_blank"></a>');
                }
                //context.invoke('editor.insertText', '');
              }
            })
          }
        }).render();
      });
      context.memo('button.editorWidth', function () {
        return ui.button({
          contents: '<i class="fas fa-text-width"></i>',
          tooltip: '调整编辑器',
          click: function (e) {
            const maxWidth = parseInt($('.note-editing-area').width());
            Swal.fire({
              title: '调整编辑器宽度',
              input: 'range',
              inputAttributes: {
                min: maxWidth / 2,
                max: maxWidth,
                step: 1
              },
              inputValue: parseInt($('.note-editable').width()) + 20
            }).then((result) => {
              console.log(result)
            });
            // 监听 input range 的值变化
            const inputRange = document.querySelector('.swal2-range input');
            inputRange.addEventListener('input', (e) => {
              $('.note-editable').css({width: e.target.value, margin: "0 auto"});
              $('.note-placeholder').css({width: e.target.value + 'px', left: (maxWidth - e.target.value) / 2});
            });
          }
        }).render();
      });
      context.memo('button.getWeiXinArticle', function () {
        return ui.button({
          contents: '<i class="fab fa-weixin"></i>',
          tooltip: '采集公众号文章',
          click: function (e) {
            Swal.fire({
              title: "微信公众号文章URL",
              input: "url",
              inputAttributes: {
                autocapitalize: "off"
              },
              showCancelButton: true,
              cancelButtonText: '取消',
              confirmButtonText: "采集",
              showLoaderOnConfirm: true,
              preConfirm: async (url) => {
                try {
                  let postData = {url: url};
                  const response = await fetch(basePath + '/admin/getWeiXinArticle', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(postData)
                  });
                  if (!response.ok) {
                    return Swal.showValidationMessage(`${JSON.stringify(await response.json())}`);
                  }
                  return response.json();
                } catch (error) {
                  Swal.showValidationMessage(`Request failed: ${error}`);
                }
              },
              allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
              if (result.isConfirmed) {
                if (context.options.loadWeiXinArticle) context.options.loadWeiXinArticle(result.value);
                else context.invoke('code', result.value.content);
              }
            });
          }
        }).render();
      });
      context.memo('button.clearWord', function () {
        return ui.button({
          contents: '<i class="fa-solid fa-eraser"></i>',
          tooltip: '清除所有格式',
          click: function () {
            // 1. 移除换行符和Mso类
            let code = context.invoke('code').replace(/(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g, ' ');
            // 2. 移除Word生成的HTML注释和&nbsp;
            code = code.replace(new RegExp('<!--(.*?)-->|&nbsp;', 'g'), '');
            // 3. 保留段落（<p>）、标题（<h1> - <h6>）、图片（<img>）、表格和换行，移除其他所有标签
            const allowedTags = /<(\/?(p|h[1-6]|img|table|tr|th|td|br))[^>]*>/g;
            code = code.replace(/<[^>]*>/g, function (match) {
              if (match.startsWith('<img')) {
                const srcMatch = match.match(/src="([^"]*)"/);
                return srcMatch ? '<img src="' + srcMatch[1] + '" style="max-width: 100%">' : '';
              } else if (match.startsWith('<table')) return '<table class="table table-bordered">'
              else if (match.startsWith('<br')) return '<br>'
              else return match.match(allowedTags) ? match.replace(/ [^=\s]+="[^"]*"/g, '') : '';
            });
            context.invoke('code', code);
          }
        }).render();
      });
      context.memo('button.pasteText', function () {
        return ui.button({
          contents: '<i class="fa-solid fa-paste"></i>',
          tooltip: '粘贴纯文本',
          click: function () {
            navigator.clipboard.readText().then((copiedText) => {
              context.invoke('pasteHTML', copiedText.replace(/([\r\n]+|\n+)/g, '<p>'));
            }).catch((error) => {
              if (error.toString().includes('Read permission denied'))
                Toast.fire({icon: 'error', title: '您未授权使用剪贴板', text: '点击地址栏前面的小锁开启授权'});
            });
          }
        }).render();
      });
      context.memo('button.deleteCode', function () {
        return ui.button({
          contents: '<i class="fa-solid fa-trash"></i>',
          tooltip: '清空编辑器',
          click: function () {
            context.invoke('code', '');
          }
        }).render();
      });
      context.memo('button.material', function () {
        return ui.button({
          contents: '<i class="fa-solid fa-photo-film"></i>',
          tooltip: '从素材库选择',
          click: function () {
            $.ajax({
              url: basePath + '/admin/material?summernoteId=' + context.layoutInfo.note[0].id,
              type: 'GET',
              success: function (body) {
                modalDialog('素材库', body, 'modal-xl');
              },
              error: errorDialog
            });
          }
        }).render();
      });
      context.memo('button.135editor', function () {
        var editor135;

        function onContentFrom135(event) {
          if (typeof event.data !== 'string') {
            if (event.data.ready) {
              editor135.postMessage(context.invoke('code'), '*');
            }
            return;
          }

          if (event.data.indexOf('<') !== 0) return;

          context.invoke('code', event.data)
          window.removeEventListener('message', onContentFrom135);
        }

        return ui.button({
          contents: '135',
          tooltip: '135编辑器',
          click: function () {
            editor135 = window.open('https://www.135editor.com/beautify_editor.html?callback=true&appkey=', '135editor', 'height=' + (window.screen.availHeight - 100) + ',width=' + (window.screen.availWidth - 100) + ',top=50,left=50,help=no,resizable=no,status=no,scroll=no')

            window.removeEventListener('message', onContentFrom135);
            window.addEventListener('message', onContentFrom135, false);

          }
        }).render();
      });
      context.memo('button.viewNews', function () {
        return ui.button({
          contents: '<i class="fa-solid fa-qrcode"></i>',
          tooltip: '预览新闻',
          click: function () {
            Swal.fire({
              imageUrl: basePath + "/admin/viewNews/" + basic_id,
              confirmButtonText: '好的'
            });
          }
        }).render();
      });
      context.memo('button.insertImage', function () {
        return ui.buttonGroup([ui.button({
          className: 'dropdown-toggle',
          contents: ui.dropdownButtonContents('<i class="fa-solid fa-images"></i>', $.summernote.options),
          tooltip: '插入图片',
          data: {
            'bs-toggle': 'dropdown'
          }
        }), ui.dropdown([ui.buttonGroup({
          className: 'note-align',
          children: [
            ui.button({
              contents: '<i class="fa-solid fa-image"></i>',
              tooltip: '本地上传',
              click: context.createInvokeHandler('imageDialog.show')
            }),
            ui.button({
              contents: '<i class="fas fa-photo-film"></i>',
              tooltip: '从素材库选择',
              click: function (e) {
                $.ajax({
                  url: basePath + '/admin/material?summernoteId=' + context.layoutInfo.note[0].id,
                  type: 'GET',
                  success: function (body) {
                    modalDialog('素材库', body, 'modal-xl');
                  },
                  error: errorDialog
                });
              }
            })
          ]
        })])]).render();
      });
      // 添加模板按钮
      if (context.options.templates && context.options.templates.length) {
        context.memo('button.useTemplate', function () {
          let buttonChildren = [];
          for (const data of context.options.templates) {
            buttonChildren.push(ui.button({
              contents: data.title,
              className: 'dropdown-item',
              tooltip: '使用模板',
              click: function () {
                if (context.invoke('editor.isEmpty')) context.invoke('code', data.content);
                else dialog('确定使用模板', '是否使用模板替换当前编辑器内容？', function () {
                  context.invoke('code', data.content);
                });
              }
            }));
          }

          return ui.buttonGroup([ui.button({
            className: 'dropdown-toggle',
            contents: ui.dropdownButtonContents('<i class="fa-solid fa-book"></i>', context.options),
            tooltip: '选择新闻模板',
            data: {
              'bs-toggle': 'dropdown'
            }
          }), ui.dropdown([ui.buttonGroup({
            className: 'btn-group-vertical w-100',
            children: buttonChildren
          })])]).render();
        });
      }

      context.memo('button.customStyle', function () {
        return ui.buttonGroup([ui.button({
          className: 'dropdown-toggle',
          contents: ui.dropdownButtonContents('<i class="fa-solid fa-xmarks-lines"></i>', context.options),
          tooltip: '选择边框模板',
          data: {
            'bs-toggle': 'dropdown'
          }
        }), ui.dropdown({
          className: 'dropdown-style',
          items: [
            {
              template: '<section style="margin: 0; padding: 3px; outline: 0; font-weight: bold; color:#366092; border: 1px solid rgb(149, 179, 215)" >' +
                '<p style="padding:.5em; margin: 0; background-color: rgb(198, 217, 240)">请按Ctrl+Shift+V粘贴内容<br></p>' +
                '</section>'
            }, {
              template: '<section style="margin: 0; padding: 20px 15px;; outline: 0; border: 1px solid rgb(54, 96, 146); box-shadow: rgb(84, 141, 212) 3px 3px 0;" >' +
                '<p>请按Ctrl+Shift+V粘贴内容<br></p>' +
                '</section>'
            },
            {
              template: '<section style="margin: 0; padding: 20px 15px;; outline: 0; border: 1px solid rgb(88, 165, 231); border-radius: 8px;" >' +
                '<p>请按Ctrl+Shift+V粘贴内容<br></p>' +
                '</section>'
            }, {
              template: '<section style="margin: 0; padding: 20px 15px; outline: 0; border: 2px dashed rgb(54, 96, 146); border-radius: 8px;" >' +
                '<p>请按Ctrl+Shift+V粘贴内容<br></p>' +
                '</section>'
            }
          ],
          title: '边框模板',
          template: function template(item) {
            return item.template;
          },
          click: function (event) {
            const element = $($(event.target).closest('a').html());
            context.invoke('editor.insertNode', element[0]);
            return false;
          }
        })]).render();
      });
    }
  });
}));
