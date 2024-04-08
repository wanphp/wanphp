var adminDataTables;
$(document).ready(function () {
  $('body').on('click', '#admin-adminList .card-header span[data-id]', function (e) {
    if ($(this).hasClass('btn-secondary')) {
      $('.card-header span[data-id].btn-success').removeClass('btn-success').addClass('btn-secondary');
      $(this).removeClass('btn-secondary').addClass('btn-success');
      $("#adminForm select[name='role_id']").val($(this).attr('data-id')).trigger("change");
      adminDataTables.ajax.reload();
    }
  }).on('click', '#admin-adminList #adminData thead .dropdown-item', function (e) {
    $(e.currentTarget).parents('ul.dropdown-menu').find('a.active').removeClass('active');
    $(e.currentTarget).addClass('active');
    adminDataTables.ajax.reload();
  }).on('click', '#randomPwd', function () {
    const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,16}$/;
    let password = "";
    do {
      password = generateRandomString(Math.floor(Math.random() * 9) + 8);
    } while (!regex.test(password)); // 检查密码是否满足要求
    // 返回生成的密码
    $("#adminForm input[name='password']").attr('type', 'text').val(password);
  }).on('click', '#admin-adminList #adminData tbody button', function () {
    const data = adminDataTables.row($(this).parents('tr')).data();
    if ($(this).hasClass('edit')) {
      if (!$('#collapseAdmin').hasClass('show')) $('#collapseAdmin').collapse('show');
      $('#adminForm').attr('action', basePath + '/admin/admins/' + data.id).attr('method', 'PUT');
      $("#adminForm input[name='account']").val(data.account);
      $("#adminForm select[name='role_id']").val(data.role_id).trigger("change");
      $("#adminForm select[name='groupId']").val(data.groupId).trigger("change");
      $("#adminForm select[name='status']").val(data.status);
      $("#adminForm input[name='name']").val(data.name);
      $("#adminForm input[name='tel']").val(data.tel);
      if (data.uid > 0) {
        $("#adminForm select[name='uid']").append(new Option(data.weuser.nickname, data.uid, true, true)).trigger('change');
      }
    }
    if ($(this).hasClass('del')) {
      const delRow = $(this).parents('tr');
      dialog('删除管理员', '是否确认删除管理员', function () {
        $.ajax({
          url: basePath + '/admin/admins/' + data.id,
          type: 'POST',
          headers: {"X-HTTP-Method-Override": "DELETE"},
          dataType: 'json',
          success: function (data) {
            adminDataTables.row(delRow).remove().draw(false);
            Swal.fire({icon: 'success', title: '删除成功！', showConfirmButton: false, timer: 1500});
          },
          error: errorDialog
        });
      });
    }
  }).on('click', '#admin-adminList #adminForm .btn-secondary', function () {
    $('#collapseAdmin').collapse('hide');
  }).on('submit', '#admin-adminList #adminForm', function (e) {
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
          let data;
          if ($(e.target).attr('method') === 'PUT') {
            const id = $(e.target).attr('action').split('/').pop();
            data = adminDataTables.row($("tr[id='" + id + "']")).data();
            data['account'] = fromData.get('account');
            data['status'] = fromData.get('status');
            adminDataTables.row($("tr[id='" + id + "']")).data(data).draw(false);
          } else {
            data = {
              id: json.id,
              account: fromData.get('account')
            };
            adminDataTables.row.add(data).draw(false);
          }
          $('#admin-adminList #collapseAdmin').collapse('hide');
        },
        error: errorDialog
      });
    }
  }).on('hidden.bs.collapse', '#admin-adminList #collapseAdmin', function () {
    $("#adminForm select[name='uid'] option").remove();
    $('#adminForm').removeClass('was-validated').attr('action', basePath + '/admin/admins').attr('method', 'POST')[0].reset();
  });
});
