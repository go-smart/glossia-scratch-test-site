var available_arguments = [];

$(function () {
  $('#context-choice').change(function(ev) {
    var id = $(this).val();
    if (id < 0)
      return;

    $.getJSON('/combination', { context_id: id, output: 'Modality' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, modality) {
        entries += "<option value='" + id + "'>" + modality + "</option>\n";
      });
      $('#modality-choice').html(entries);
      $('#power-generator-choice').html("<option value='-1' disabled selected>Choose modality first</option>");
      $('#needle-choice').html("<option value='-1' disabled selected>Choose generator first</option>");
      $('#numerical-model-choice').html("<option value='-1' disabled selected>Choose needle first</option>");
    });
  });

  $('#modality-choice').change(function(ev) {
    var id = $(this).val();
    var c = $('#context-choice').val();

    if (id < 0)
      return;

    $.getJSON('/combination', { context_id: c, modality_id: id, output: 'PowerGenerator' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, generator) {
        entries += "<option value='" + id + "'>" + generator + "</option>\n";
      });
      $('#power-generator-choice').html(entries);
      $('#needle-choice').html("<option value='-1' disabled selected>Choose generator first</option>");
      $('#numerical-model-choice').html("<option value='-1' disabled selected>Choose needle first</option>");
    });
  });

  $('#power-generator-choice').change(function(ev) {
    var id = $(this).val();
    var c = $('#context-choice').val();

    if (id < 0)
      return;

    $.getJSON('/combination', { context_id: c, power_generator_id: id, output: 'Needle' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, needle) {
        entries += "<option value='" + id + "'>" + needle + "</option>\n";
      });
      $('#needle-choice').html(entries);
      $('#numerical-model-choice').html("<option value='-1' disabled selected>Choose needle first</option>");
    });
  });

  $('#needle-choice').change(function(ev) {
    var current = $(this).val();
    var c = $('#context-choice').val();
    var pg = $('#power-generator-choice').val();

    if (current < 0)
      return;

    $.getJSON('/combination', { context_id: c, power_generator_id: pg, needle_id: current, output: 'NumericalModel' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, model) {
        entries += "<option value='" + id + "'>" + model + "</option>\n";
      });
      $('#numerical-model-choice').html(entries);
    });
  });
});
