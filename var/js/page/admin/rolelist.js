var roleDataTables;
$(document).ready(function () {
  $('body').on('click', '#rolesData tbody button', function () {
    const role = roleDataTables.row($(this).parents('tr')).data();
    // console.log(role);
    if ($(this).hasClass('edit')) {
      $('#admin-roleList #roleForm').attr('action', basePath + '/admin/roles/' + role.id).attr('method', 'PUT');
      $("#admin-roleList #roleForm input[name='name']").val(role.name);
      if (role.restricted) for (const value of role.restricted) {
        $("#admin-roleList #restricted").append($('#admin-roleList #roleForm #route option[value="' + value + '"]'));
      }
      $('#admin-roleList #modal-addRole').modal('show');
    }
    if ($(this).hasClass('del')) {
      const delRow = $(this).parents('tr');
      dialog('删除角色', '是否确认删除角色', function () {
        $.ajax({
          url: basePath + '/admin/roles/' + role.id,
          type: 'POST',
          headers: {"X-HTTP-Method-Override": "DELETE"},
          dataType: 'json',
          success: function () {
            roleDataTables.row(delRow).remove().draw(false);
            Swal.fire({icon: 'success', title: '删除成功！', showConfirmButton: false, timer: 1500});
          },
          error: errorDialog
        });
      });
    }
  }).on('submit', '#admin-roleList #roleForm', function (e) {
    console.log(e.target.checkValidity());
    if (e.target.checkValidity()) {
      const fromData = new FormData(e.target);
      const restricted = [];
      $('#admin-roleList #restricted option').each(function (index, option) {
        restricted.push($(option).attr('value'));
      })
      fromData.append('restricted', JSON.stringify(restricted));
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
          let roleId;
          if ($(e.target).attr('method') === 'PUT') roleId = $(e.target).attr('action').split('/').pop();
          else roleId = json.id;

          const data = {
            id: roleId,
            name: fromData.get('name'),
            restricted: restricted,
            op: '<button type="button" class="btn btn-tool edit" data-bs-toggle="tooltip" data-bs-title="修改"><i class="fas fa-edit"></i></button>' +
              '<button type="button" class="btn btn-tool del" data-bs-toggle="tooltip" data-bs-title="删除"><i class="fas fa-trash-alt"></i></button>'
          };
          if ($(e.target).attr('method') === 'PUT') {
            roleDataTables.row($("tr[id='" + roleId + "']")).data(data);
          } else {
            roleDataTables.row.add(data).draw(false);
          }
          $('#modal-addRole').modal('hide');
        },
        error: errorDialog
      });
    }
  }).on('change', '#admin-roleList #route', function () {
    $('#admin-roleList #restricted').append($(this).find('option:selected'));
  }).on('change', '#admin-roleList #restricted', function () {
    $('#admin-roleList #route').append($(this).find('option:selected'));
  }).on('hidden.bs.modal', '#admin-roleList #modal-addRole', function () {
    $('#admin-roleList #roleForm').attr('action', basePath + '/admin/roles').attr('method', 'POST').removeClass('was-validated')[0].reset();
    $("#admin-roleList #route").append($('#admin-roleList #restricted option'));
  });
});
