var groupDataTables;
$(document).ready(function () {
  $('body').on('click', '#admin-groupList #groupData tbody button', function () {
    const group = groupDataTables.row($(this).parents('tr')).data();
    if ($(this).hasClass('edit')) {
      $('#admin-groupList #groupForm').attr('action', basePath + '/admin/group/' + group.id);
      $('#admin-groupList #groupForm').attr('method', 'PUT');
      $("#admin-groupList #groupForm input[name='name']").val(group.name);
      $("#admin-groupList #groupForm textarea[name='description']").val(group.description);
      $("#admin-groupList #groupForm input[name='displayOrder']").val(group.displayOrder);
      $('#admin-groupList #modal-addGroup').modal('show');
    }
    if ($(this).hasClass('del')) {
      const delRow = $(this).parents('tr');
      dialog('删除分组', '是否确认删除分组', function () {
        $.ajax({
          url: basePath + '/admin/group/' + group.id,
          type: 'POST',
          headers: {"X-HTTP-Method-Override": "DELETE"},
          dataType: 'json',
          success: function (data) {
            groupDataTables.row(delRow).remove().draw(false);
            Swal.fire({icon: 'success', title: '删除成功！', showConfirmButton: false, timer: 1500});
          },
          error: errorDialog
        });
      });
    }
  }).on('submit', '#admin-groupList #groupForm', function (e) {
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
          let groupId;
          if ($(e.target).attr('method') === 'PUT') groupId = $(e.target).attr('action').split('/').pop();
          else groupId = json.id;

          const data = {
            id: groupId,
            name: fromData.get('name'),
            description: fromData.get('description'),
            displayOrder: fromData.get('displayOrder'),
            op: '<button type="button" class="btn btn-tool edit" data-bs-toggle="tooltip" data-bs-title="修改"><i class="fas fa-edit"></i></button>' +
              '<button type="button" class="btn btn-tool del" data-bs-toggle="tooltip" data-bs-title="删除"><i class="fas fa-trash-alt"></i></button>'
          };
          if ($(e.target).attr('method') === 'PUT') {
            groupDataTables.row($("tr[id='" + groupId + "']")).data(data);
          } else {
            groupDataTables.row.add(data).draw(false);
          }
          $('#admin-groupList #modal-addGroup').modal('hide');
        },
        error: errorDialog
      });
    }
  }).on('hidden.bs.modal', '#admin-groupList #modal-addGroup', function () {
    $('#admin-groupList #groupForm').attr('action', basePath + '/admin/group').attr('method', 'POST')[0].reset();
  });
});
