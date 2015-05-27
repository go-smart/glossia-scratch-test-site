var available_arguments = [];

$(function () {
  $('#modality-choice').change(function(ev) {
    var id = $(this).val();
    if (id < 0)
      return;

    $.getJSON('/power_generator', { modality: id }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, generator) {
        entries += "<option value='" + id + "'>" + generator + "</option>\n";
      });
      $('#power-generator-choice').html(entries);
      $('#needle-choice').html("<option value='-1' disabled selected>Choose generator first</option>");
    });

    $.getJSON('/numerical_model', { modality: id }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, model) {
        entries += "<option value='" + id + "'>" + model + "</option>\n";
      });
      $('#numerical-model-choice').html(entries);
    });
  });

  $('#power-generator-choice').change(function(ev) {
    var id = $(this).val();
    if (id < 0)
      return;

    $.getJSON('/needle', { power_generator: id }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, needle) {
        entries += "<option value='" + id + "'>" + needle + "</option>\n";
      });
      $('#needle-choice').html(entries);
    });
    update_parameters('power_generator', id, '#parameters-generator');
  });

  $('#numerical-model-choice').change(function(ev) {
    var current = $(this).val();
    update_parameters('numerical_model', current, '#parameters-model');

    if (current < 0)
      return;

    $.getJSON('/numerical_model/arguments', { id: current }, function(data) {
      available_arguments = [];
      $.each(data, function (idx, entry) {
        available_arguments.push(entry.id);
      });
    });
  });

  $('#needle-choice').change(function(ev) {
    var id = $(this).val();
    update_parameters('needle', id, '#parameters-needle');
  });
});

function update_parameters(type, current, target) {
  if (current < 0)
    return;

  var url = '/' + type + '/parameters';
  $.getJSON(url, { id: current }, function(data) {
    entries = "";
    $.each(data, function (idx, entry) {
      entries += "<li><input type='checkbox' value='" + entry.id + "' name='parameters[]'>" + entry.html + " (" + entry.type + ")" + "</li>\n";
    });
    $(target).html(entries);
  });
}

var algorithm_count = 0;
function add_algorithm_entry() {
  var model = $('#numerical-model-choice').val();

  if (model < 0)
  {
    alert('Please choose a model first');
    return;
  }

  info = {};
  info['arguments'] = available_arguments;

  $.ajax({ dataType: 'html', url: '/algorithm/create', data: info, success: function(data) {
    $('#algorithm-block').append(data);
    $('#algorithm-block:last-child').attr('id', 'algorithm-entry-' + algorithm_count);
    algorithm_count += 1;
  }});
}
