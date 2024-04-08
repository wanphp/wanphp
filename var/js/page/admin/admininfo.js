$(document).ready(function () {
  let checkNum = 0;
  $('body').on('submit', '#editPassword', function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (e.target.checkValidity()) {
      Swal.fire({
        title: "是否确定已记住密码？",
        text: "确定修改密码后系统将自动退出。",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: '确定',
        cancelButtonText: '没记住'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: basePath + '/admin/editPassword',
            data: new FormData(e.target),
            type: 'POST',
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function () {
              location.href = basePath + '/loginOut';
            },
            error: errorDialog
          });
        }
      });
    } else {
      Toast.fire({icon: 'error', title: '账号或密码格式不正确'});
    }
  }).on('click', '#admin-adminInfo #resetPasswd', function () {
    var regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,16}$/;
    var password = "";
    do {
      password = generateRandomString(Math.floor(Math.random() * 9) + 8);
    } while (!regex.test(password)); // 检查密码是否满足要求
    // 返回生成的密码
    $("#admin-adminInfo input[name='password']").attr('type', 'text').val(password);
  }).on('click', '#admin-adminInfo .d-block', function () {
    $('#admin-adminInfo .modal-body .info-box').remove();
    $('#bindQr').show();

    timer = setInterval(function () {
      checkNum++;
      $.post(basePath + '/admin/userBind', function (data) {
        if (data.res === 'OK') {
          clearInterval(timer);
          location.reload();
        }
      }, 'json');
      if (checkNum > 100) {
        clearInterval(timer);
        $('#bindQr').html('二维码已过期！');
      }
    }, 2000);
  }).on('hidden.bs.modal', '#modalDialog', function () {
    checkNum = 0;
    $('#admin-adminInfo').remove();
    if (timer) clearInterval(timer);
  });
});
