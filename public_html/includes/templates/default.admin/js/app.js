// Alerts
  $('body').on('click', '.alert .close', function(e){
    e.preventDefault();
    $(this).closest('.alert').fadeOut('fast', function(){$(this).remove()});
  });

// Set Head Title
  if ($('h1').length) {
    document.title = document.title +' – '+ $('h1:first').text();
  }

// Form required asterix
  $(':input[required="required"]').closest('.form-group').addClass('required');

// AJAX Search
  var timer_ajax_search = null;
  var xhr_search = null;
  $('#search input[name="query"]').on('change input', function(){
    if (xhr_search) xhr_search.abort();

    if ($(this).val() != '') {
      if (!$('#search .loader-wrapper').length) {
        $('#box-apps-menu').fadeOut('fast');
        $('#search .results').show().html('<div class="loader-wrapper text-center"><div class="loader" style="width: 48px; height: 48px;"></div></div>');
      }
      var query = $(this).val();

      clearTimeout(timer_ajax_search);
      timer_ajax_search = setTimeout(function() {
        xhr_search = $.ajax({
          type: 'get',
          async: true,
          cache: false,
          url: 'search_results.json.php?query=' + query,
          dataType: 'json',
          beforeSend: function(jqXHR) {
            jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
          },
          error: function(jqXHR, textStatus, errorThrown) {
            $('#search .results').text(textStatus + ': ' + errorThrown);
          },
          success: function(json) {
            $('#search .results').html('');
            if (!$('#search input[name="query"]').val()) return;
            $.each(json, function(i, group){
              if (group.results.length) {
                $('#search .results').append(
                  '<h4>'+ group.name +'</h4>' +
                  '<ul class="list-group" data-group="'+ group.name +'"></ul>'
                );
                $.each(group.results, function(i, result){
                  $('#search .results ul[data-group="'+ group.name +'"]').append(
                    '<li class="result">' +
                    '  <a class="list-group-item" href="'+ result.url +'">' +
                    '    <small class="id pull-right">#'+ result.id +'</small>' +
                    '    <div class="title">'+ result.title +'</div>' +
                    '    <div class="description"><small>'+ result.description +'</small></div>' +
                    '  </a>' +
                    '  <hr style="border-bottom: 3px '+ group.theme.color +' solid;" />' +
                    '</li>'
                  );
                });
              }
            });
            if ($('#search .results').html() == '') {
              $('#search .results').html('<p class="text-center no-results"><em>:(</em></p>');
            }
          },
        });
      }, 500);

    } else {
      $('#search .results').hide().html('');
      $('#box-apps-menu').fadeIn('fast');
    }
  });

// Bootstrap Comaptible (data-toggle="tab")
  $('body').on('click', '[data-toggle="tab"]', function(e) {
    e.preventDefault();
    $(this).closest('ul').find('li').removeClass('active');
    $(this).closest('li').addClass('active');
    $($(this).attr('href')).show().siblings().hide();
  });

  $('.nav-tabs').each(function(){
    if (!$(this).find('.active').length) {
      $(this).find('li:first').addClass('active');
    }
  });

  $('.nav-tabs .active a').trigger('click');
  if (document.location.hash != '') {
    $('a[href="' + document.location.hash + '"]').click();
  }

// Bootstrap Comaptible (data-toggle="buttons")
  $('body').on('click', '[data-toggle="buttons"] input[type="checkbox"]', function(){
    if ($(this).is(':checked')) {
      $(this).closest('.btn').addClass('active');
    } else {
      $(this).closest('.btn').removeClass('active');
    }
  });

  $('body').on('click', '[data-toggle="buttons"] input[type="radio"]', function(){
    $(this).closest('.btn').addClass('active').siblings().removeClass('active');
  });

// Data-Table Toggle Checkboxes
  $('body').on('click', '.data-table *[data-toggle="checkbox-toggle"]', function() {
    $(this).closest('.data-table').find('tbody :checkbox').each(function() {
      $(this).prop('checked', !$(this).prop('checked'));
    });
    return false;
  });

  $('.data-table tbody tr').click(function(e) {
    if ($(e.target).is(':input')) return;
    if ($(e.target).is('a, a *')) return;
    if ($(e.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });