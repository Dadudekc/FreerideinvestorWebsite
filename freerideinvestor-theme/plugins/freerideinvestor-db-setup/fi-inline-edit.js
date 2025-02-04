jQuery(document).ready(function($) {
  // --- Inline Edit Handling ---
  $('#fi-inline-table').on('click', '.fi-edit-button', function() {
    let $button = $(this);
    let $row    = $button.closest('tr');
    
    // If it's currently "Edit", switch to input fields
    if ($button.text() === 'Edit') {
      $row.find('.fi-editable').each(function() {
        let textVal = $(this).text();
        $(this).html('<input type="text" class="fi-inline-input" value="'+ textVal +'">');
      });
      $button.text('Save');
    }
    // Otherwise, "Save" the updates via AJAX
    else {
      let user_id  = $row.data('userid');
      let username = $row.find('[data-field="username"] input').val();
      let email    = $row.find('[data-field="email"] input').val();

      $.ajax({
        url: fiInlineEdit.ajaxUrl,
        method: 'POST',
        data: {
          action: 'fi_update_user',
          security: fiInlineEdit.nonce,
          user_id: user_id,
          username: username,
          email: email
        },
        success: function(response) {
          if (response.success) {
            // Update table cells with new text
            $row.find('[data-field="username"]').text(response.data.username);
            $row.find('[data-field="email"]').text(response.data.email);
            $button.text('Edit');
          } else {
            alert('Error: ' + (response.data.message || 'Unknown'));
          }
        },
        error: function(err) {
          alert('An error occurred updating the user.');
          console.log(err);
        }
      });
    }
  });

  // --- Delete Handling ---
  $('#fi-inline-table').on('click', '.fi-delete-button', function() {
    if(!confirm('Are you sure you want to delete this user?')) {
      return;
    }

    let $row   = $(this).closest('tr');
    let userID = $row.data('userid');

    $.ajax({
      url: fiInlineEdit.ajaxUrl,
      method: 'POST',
      data: {
        action: 'fi_delete_user',
        security: fiInlineEdit.nonce,
        user_id: userID
      },
      success: function(response) {
        if (response.success) {
          $row.remove(); // Remove row from DOM
          alert('User deleted successfully!');
        } else {
          alert('Error: ' + (response.data.message || 'Unknown'));
        }
      },
      error: function(err) {
        alert('An error occurred deleting the user.');
        console.log(err);
      }
    });
  });
});
