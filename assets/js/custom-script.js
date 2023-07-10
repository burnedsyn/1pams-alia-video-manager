(function ($) {
  $(document).ready(function () {
    $('.pams-vimeo-import').click(function () {
      var index = $(this).data('index');
      // Call your desired JavaScript function with the index value
      pamsVimeoImport(index);
    });

    let intervalId;


    function pamsVimeoImport(index) {
      // Get the information from the HTML block
      var cardItem = document.getElementById(index);
      var title = cardItem.querySelector('.card-header').textContent.trim();
      var encodedTitle = encodeURIComponent(title);
      var pamsItem = cardItem.querySelector('.pamsitem');
      var uri = pamsItem.querySelector('p:nth-child(1)').textContent.split(':')[1].trim();
      var videoLink = pamsItem.querySelector('p:nth-child(2)').textContent.match(/Video link\s*:\s*(.*)/i)[1].trim();
      // Define a variable to store the interval ID

      var description = pamsItem.querySelector('p:nth-child(3)').textContent.split(':')[1].trim();
      var selector = 'input[type="hidden"]';
      var hiddenInput = cardItem.querySelector(selector);
      if (hiddenInput) {
        var hiddenValue = hiddenInput.value;
        console.log(hiddenValue);
      } else {
        console.log('Hidden input not found for index: ' + index);
      }

      var ajaxurl = 'admin-ajax.php';
      // Create an object to hold the data
      var data = {
        index: index,
        uri: uri,
        videoLink: videoLink,
        description: description,
        nonce: hiddenInput.value,
        title: encodedTitle
      };

      // Make an AJAX request to send the data to the PHP function
      jQuery.ajax({
        url: ajaxurl, // Make sure to localize this URL or provide the appropriate URL to your PHP function
        type: 'POST',
        data: {
          action: 'pams_vimeo_import', // Replace with your PHP function name
          data: data
        },
        success: function (response) {
          // Handle the response from the PHP function if needed
          cardItem.classList.add('start');
          cardItem.classList.remove('warning');

          var btnInput = cardItem.querySelector('.pams-vimeo-import');
          var spinner = document.createElement('span');
          spinner.classList.add('pams-spinner', 'spinner-border', 'spinner-border-sm');
          btnInput.innerHTML = ''; // Clear existing content of the button
          btnInput.appendChild(spinner); // Append the spinner element to the button
          btnInput.insertAdjacentText('beforeend', ' Start ovh export'); // Append the text after the spinner
          btnInput.classList.remove('btn-dark');
          btnInput.classList.add('btn-secondary');
          var jsonResponse = JSON.parse(response);

          var logpath = decodeURIComponent(jsonResponse.file_Path);
          // Remove the old click event handler
          $(btnInput).off('click');
          // Attach another click handler to the button
          btnInput.addEventListener('click', function () {
            export2ovh(logpath, index);
          });
          var spinner = btnInput.querySelector('.pams-spinner');

          // Show the spinner
          spinner.style.display = 'inline-block';

          btnInput.disabled = true;



          // Start the interval and store the ID
          intervalId = setInterval(function () {
            getStatus(logpath, index, intervalId);
          }, 10000);

          console.log(response);

        },
        error: function (error) {
          // Handle any error that occurred during the AJAX request
          console.log(error);
        }
      });

    }
    function export2ovh(logpath, index) {
      var ajaxurl = 'admin-ajax.php';
      var cardItem = document.getElementById(index);
      var btnInput = cardItem.querySelector('.pams-vimeo-import');
      cardItem.classList.add('start');
      cardItem.classList.remove('done');
      btnInput.classList.remove('btn-primary');
      btnInput.classList.add('btn-info');
      var spinner = document.createElement('span');
      spinner.classList.add('pams-spinner', 'spinner-border', 'spinner-border-sm');
      btnInput.innerHTML = ''; // Clear existing content of the button
      btnInput.appendChild(spinner); // Append the spinner element to the button
      btnInput.insertAdjacentText('beforeend', ' Start ovh export'); // Append the text after the spinner
      btnInput.classList.remove('btn-dark');
      btnInput.classList.add('btn-secondary');
      btnInput.disabled = false;
      var title = cardItem.querySelector('.card-header').textContent.trim();
      var encodedTitle = encodeURIComponent(title);
      var selector = 'input[type="hidden"]';
      var hiddenInput = cardItem.querySelector(selector);
      if (hiddenInput) {
        var hiddenValue = hiddenInput.value;
        console.log(hiddenValue);
      } else {
        console.log('Hidden input not found for index: ' + index);
      }




      // Create an object to hold the data
      var data = {
        action: 'pams_upcloud',
        index: index,
        logpath: logpath,
        nonce: hiddenInput.value,
        title:encodedTitle
      };
      jQuery.ajax({
        url: ajaxurl, // Make sure to localize this URL or provide the appropriate URL to your PHP function
        type: 'POST',
        data: data,
        success: function (response) {
          var jsonResponse = JSON.parse(response.data);

          // Assuming the response contains operation and status information
          if (jsonResponse.operation && jsonResponse.status) {
            updateStatus(jsonResponse);

            if (jsonResponse.operation === 'process' && jsonResponse.status === 'done') {
              // Perform actions for process completion
              // E.g., redirect to another page or display a message


            }
          }
        }
      });



    }
    function getStatus(logpath, index, intervalId) {
      var ajaxurl = 'admin-ajax.php';
      // Create an object to hold the data
      var data = {
        action: 'pams_vimeo_import_status',
        index: index,
        logpath: logpath
      };
      jQuery.ajax({
        url: ajaxurl, // Make sure to localize this URL or provide the appropriate URL to your PHP function
        type: 'POST',
        data: data,
        success: function (response) {
          var jsonResponse = JSON.parse(response.data);

          // Assuming the response contains operation and status information
          if (jsonResponse.operation && jsonResponse.status) {
            updateStatus(jsonResponse);

            if (jsonResponse.operation === 'process' && jsonResponse.status === 'done') {
              // Perform actions for process completion
              // E.g., redirect to another page or display a message

              var cardItem = document.getElementById(index);
              var btnInput = cardItem.querySelector('.pams-vimeo-import');
              cardItem.classList.add('done');
              cardItem.classList.remove('start');
              btnInput.classList.remove('btn-secondary');
              btnInput.classList.add('btn-primary');
              var spinner = document.createElement('span');
              spinner.classList.add('pams-spinner', 'spinner-border', 'spinner-border-sm');
              btnInput.innerHTML = ''; // Clear existing content of the button
              btnInput.appendChild(spinner); // Append the spinner element to the button
              btnInput.insertAdjacentText('beforeend', ' Start ovh export'); // Append the text after the spinner
              /* var hiddenInfo = document.createElement() */
              spinner.style.display = 'none';
              btnInput.disabled = false;
              clearInterval(intervalId);

            }
          }
        }
      });
    }
    function updateStatus(jsonResponse) {
      console.log(jsonResponse);
    }


  });
})(jQuery);
