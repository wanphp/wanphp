var settingDataTables;
$(document).ready(function () {
  $('body').on('click', '#common-setting #settingData tbody button', function () {
    const setting = settingDataTables.row($(this).parents('tr')).data();
    //console.log(setting);
    if ($(this).hasClass('edit')) {
      $('#common-setting #setForm').attr('action', basePath + '/admin/setting/' + setting.id).attr('method', 'PUT');
      $("#common-setting #setForm input[name='name']").val(setting.name);
      $("#common-setting #setForm input[name='key']").val(setting.key);
      $("#common-setting #setForm textarea[name='value']").val(setting.value);
      $("#common-setting #setForm input[name='sortOrder']").val(setting.sortOrder);
      $('#common-setting #modal-addSetting').modal('show');
    }
    if ($(this).hasClass('del')) {
      const delRow = $(this).parents('tr');
      dialog('删除配置', '是否确认删除配置项', function () {
        $.ajax({
          url: basePath + '/admin/setting/' + setting.id,
          type: 'POST',
          headers: {"X-HTTP-Method-Override": "DELETE"},
          dataType: 'json',
          success: function (data) {
            settingDataTables.row(delRow).remove().draw(false);
            Swal.fire({icon: 'success', title: '删除成功！', showConfirmButton: false, timer: 1500});
          },
          error: errorDialog
        });
      });
    }
  }).on('submit','#common-setting #setForm',function (e) {
    console.log(e.target.checkValidity());
    if (e.target.checkValidity()) {
      const fromData = new FormData(e.target);
      $.ajax({
        url: $(e.target).attr('action'),
        data: fromData,
        type: 'POST',
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-HTTP-Method-Override", $(e.target).attr('method'));
        },
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (json) {
          let set_id;
          if ($(e.target).attr('method') === 'PUT') {
            set_id = $(e.target).attr('action').split('/').pop();
          } else {
            set_id = json.id;
          }
          const data = {
            id: set_id,
            name: fromData.get('name'),
            key: fromData.get('key'),
            value: fromData.get('value'),
            sortOrder: fromData.get('sortOrder'),
            op: '<button type="button" class="btn btn-tool edit" data-bs-toggle="tooltip" data-bs-title="修改"><i class="fas fa-edit"></i></button>' +
              '<button type="button" class="btn btn-tool del" data-bs-toggle="tooltip" data-bs-title="删除"><i class="fas fa-trash-alt"></i></button>'
          };
          if ($(e.target).attr('method') === 'PUT') {
            settingDataTables.row($("tr[id='" + set_id + "']")).data(data).draw(false);
          } else {
            settingDataTables.row.add(data).draw(false);
          }
          $('#modal-addSetting').modal('hide');
        },
        error: errorDialog
      });
    }
  }).on('click','#common-setting #upload_image', function () {
    $.uploadFile({
      url: 'https://images.ztnews.net/upload/thumb',
      uid: currentUser.uid,
      accept: 'image/jpg,image/jpeg,image/png,image/gif',
      ext: '.jpg,.jpeg,.gif,.png',
      success: function (res) {
        $('#setForm #value').val(res.url);
      },
      error: function (res) {
        Toast.fire({icon: 'error', title: res.errMsg});
      }
    });
  }).on('hidden.bs.modal', '#common-setting #modal-addSetting',function () {
    $('#common-setting #setForm').attr('action', basePath + '/admin/setting').attr('method', 'POST')[0].reset();
  });
});
