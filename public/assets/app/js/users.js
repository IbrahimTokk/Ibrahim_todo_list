$(document).ready(function(){

  const userTable = $('#users').DataTable();

  // edit user func
  function editUser(userId) {
    // reset notification
    $('#user_form_modal .notification').html("");
    $('#user_form_modal .notification').css('display', 'none');

    // set current user which is selected
    if (userId === 'new') {
      currentUser = {};
      $('#user_form_modal .modal-title').html('Add User');
    } else {
      currentUser = users.filter(user => user.id === userId)[0];
      $('#user_form_modal .modal-title').html('Edit User');
    }

    // initiate modal with selected user info
    $('#user_form_modal #user_name').val(currentUser.name);
    $('#user_form_modal #user_email').val(currentUser.email);
    $('#user_form_modal #user_age').val(currentUser.age);
  }
  
  // handle user form submit
  function handleUserFormSubmit() {
    const formData = {
      name: $('#user_form_modal #user_name').val(),
      email: $('#user_form_modal #user_email').val(),
      age: $('#user_form_modal #user_age').val(),
    }
    const submitUrl = currentUser.id ? `/api/users/${currentUser.id}` : '/api/users';

    if (currentUser.id) {
      formData['_method'] = 'PUT';
    }

    $.ajax({
      type: "POST",
      url: submitUrl,
      data: JSON.stringify(formData),
      contentType: "application/json",
      dataType: "json"
    }).done(function (res) {
      // set notification text
      $('#user_form_modal .notification').removeClass('alert-danger').addClass('alert-success');
      $('#user_form_modal .notification').html(`The user was ${currentUser.id ? 'edited' : 'saved'} successfully`);
      $('#user_form_modal .notification').css('display', 'block');

      // update users list
      if (currentUser.id) {
        // update existing user
        currentUser.name = res.user.name;
        currentUser.email = res.user.email;
        currentUser.age = res.user.age;

        $(`table#users .user-row-${currentUser.id} .name`).html(res.user.name);
        $(`table#users .user-row-${currentUser.id} .email`).html(res.user.email);
        $(`table#users .user-row-${currentUser.id} .age`).html(res.user.age);
      } else {
        // add a new user
        users.push(res.user);

        $('table#users').DataTable().destroy();
        $('table#users tbody').append(`<tr class="user-row-${res.user.id}"><td class="id">${res.user.id}</td><td class="email">${res.user.email}</td><td class="name">${res.user.name}</td><td class="age">${res.user.age}</td><td><a href="javascript: void(0)" class="fas fa-edit" id="user_edit_btn" data-user-id="${res.user.id}" data-toggle="modal" data-target="#user_form_modal"></a></td></tr>`);
        $('table#users').DataTable().draw();
      }
    }).fail(function (err) {
      // set notification text
      $('#user_form_modal .notification').removeClass('alert-success').addClass('alert-danger');
      $('#user_form_modal .notification').html(err.responseJSON);
      $('#user_form_modal .notification').css('display', 'block');
    });
  }

  // events registration
  $(document).on('click', '#user_create_btn', function() {
    editUser('new');
  });

  $(document).on('click', '#user_edit_btn', function() {
    editUser($(this).data('user-id'));
  });

  $(document).on('click', '#user_form_submit_btn', function() {
    handleUserFormSubmit();
  });

});
