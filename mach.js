(function($) {
  // Wait for the DOM to be ready
  $(function() {
    // Find the form element
    var $form = $('form#mach-form');

    // Find the textarea element
    var $textarea = $form.find('textarea#mach-content');

    // Find the submit button element
    var $submit = $form.find('input[type="submit"]');

    // Add a click event handler to the submit button
    $submit.on('click', function(event) {
      // Prevent the form from submitting
      event.preventDefault();

      // Disable the submit button
      $submit.prop('disabled', true);

      // Get the content from the textarea
      var content = $textarea.val();

      // Extract the title, tags, and category from the content
      var title = '';
      var tags = [];
      var category = '';
      var lines = content.split('\n');
      if (category === '') {
        category = machData.defaultCategory;
      }
      for (var i = 0; i < lines.length; i++) {
        var line = lines[i];
        if (line.indexOf('(') === 0 && line.indexOf(')') === line.length - 1) {
      // Extract the title from the first line in parentheses
          title = line.substring(1, line.length - 1).trim();
        }
        
      // Remove the title in parentheses from the content
      content = content.replace(/\((.*?)\)/g, '');

        // Extract tags from hashtagged words
        var words = line.split(' ');
        for (var j = 0; j < words.length; j++) {
          var word = words[j];
          if (word.indexOf('#') === 0) {
            // Remove any punctuation at the end of the word
            var tag = word.substring(1).replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g, '').trim();
            if (tag.length > 0) {
              tags.push(tag);
            }
          }
        }
      }

      // Send an AJAX request to create the post
      $.ajax({
        url: machData.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'mach_submit',
          content: content,
          title: title,
          tags: tags,
          category: category,
          nonce: machData.nonce
        },
        success: function(data) {
          if (data.success) {
            // Clear the textarea
            $textarea.val('');

            // Show a success message
            alert('Post created successfully!');

            // Re-enable the submit button
            $submit.prop('disabled', false);
          } else {
            // Show an error message
            alert(data.message);

            // Re-enable the submit button
            $submit.prop('disabled', false);
          }
        },
        error: function(xhr, status, error) {
          // Handle AJAX errors
          alert('An error occurred while creating the post. Please try again later.');

          // Re-enable the submit button
          $submit.prop('disabled', false);
        }
      });
    });
  });
})(jQuery);