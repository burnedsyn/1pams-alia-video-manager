$(document).ready(function() {
    // Function to update the status of an operation
    function updateStatus(operation, status) {
      var $item = $('.timeline-item[data-operation="' + operation + '"]');
      $item.removeClass('in-progress success error');
      $item.addClass(status);
  
      var $statusText = $item.find('.timeline-body p:first-child');
      $statusText.text('Status: ' + status.charAt(0).toUpperCase() + status.slice(1));
    }
  
    // Function to handle AJAX request
    function getStatus() {
      $.ajax({
        url: 'your-api-endpoint', // Replace with your actual API endpoint
        method: 'GET',
        success: function(response) {
          // Assuming the response contains operation and status information
          if (response.operation && response.status) {
            updateStatus(response.operation, response.status);
  
            if (response.operation === 'process' && response.status === 'done') {
              // Perform actions for process completion
              // E.g., redirect to another page or display a message
            }
          }
        }
      });
    }
  
    // Periodically update the status every 5 seconds
    setInterval(getStatus, 5000);
  });
  