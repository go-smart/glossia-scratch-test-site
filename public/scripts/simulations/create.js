var available_arguments = [];

$(function () {
  $('#context-choice').change(function(ev) {
    var id = $(this).val();
    if (id < 0)
      return;

    $.getJSON('/simulation/patient', { Context_Id: id }, function (data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, patient) {
        entries += "<option value='" + id + "'>" + patient + "</option>\n";
      });
      $('#patient-choice').html(entries);
    });

    $.getJSON('/combination', { Context_Id: id, output: 'Modality' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, modality) {
        entries += "<option value='" + id + "'>" + modality + "</option>\n";
      });
      $('#modality-choice').html(entries);
      $('#power-generator-choice').html("<option value='-1' disabled selected>Choose modality first</option>");
      $('#needle-choice').html("<option value='-1' disabled selected>Choose generator first</option>");
      $('#protocol-choice').html("<option value='-1' disabled selected>Choose generator first</option>");
      $('#numerical-model-choice').html("<option value='-1' disabled selected>Choose protocol first</option>");
    });
  });

  $('#modality-choice').change(function(ev) {
    var id = $(this).val();
    var c = $('#context-choice').val();

    if (id < 0)
      return;

    $.getJSON('/combination', { Context_Id: c, Modality_Id: id, output: 'PowerGenerator' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, generator) {
        entries += "<option value='" + id + "'>" + generator + "</option>\n";
      });
      $('#power-generator-choice').html(entries);
      $('#needle-choice').html("<option value='-1' disabled selected>Choose generator first</option>");
      $('#protocol-choice').html("<option value='-1' disabled selected>Choose generator first</option>");
      $('#numerical-model-choice').html("<option value='-1' disabled selected>Choose protocol first</option>");
    });
  });

  $('#power-generator-choice').change(function(ev) {
    var id = $(this).val();
    var c = $('#context-choice').val();

    if (id < 0)
      return;

  /*
    $.getJSON('/combination', { Context_Id: c, Power_Generator_Id: id, output: 'Needle' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, needle) {
        entries += "<option value='" + id + "'>" + needle + "</option>\n";
      });
      $('#needle-choice').html(entries);
    });
    */

    $.getJSON('/combination', { Context_Id: c, Power_Generator_Id: id, output: 'Protocol' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, model) {
        entries += "<option value='" + id + "'>" + model + "</option>\n";
      });
      $('#protocol-choice').html(entries);
    });
  });

  $('#protocol-choice').change(function(ev) {
    var current = $(this).val();
    var c = $('#context-choice').val();
    var pg = $('#power-generator-choice').val();

    if (current < 0)
      return;

    $.getJSON('/combination', { Context_Id: c, Power_Generator_Id: pg, Protocol_Id: current, output: 'NumericalModel' }, function(data) {
      entries = "<option value='-1' disabled selected>Please select</option>";
      $.each(data, function (id, numerical_model) {
        entries += "<option value='" + id + "'>" + numerical_model + "</option>\n";
      });
      $('#numerical-model-choice').html(entries);
    });
  });

  $('#numerical-model-choice').change(function(ev) {
    var current = $(this).val();
    var c = $('#context-choice').val();
    var pg = $('#power-generator-choice').val();
    var pr = $('#protocol-choice').val();

    if (current < 0)
      return;

    $.getJSON('/combination', { Context_Id: c, Power_Generator_Id: pg, Protocol_Id: pr, Numerical_Model_Id: current, output: 'Combination' }, function(data) {
      var keys = Object.keys(data);

      if (keys.length != 1)
        alert('Error - ' + keys.length + ' combinations found!');

      var key = keys[0];
      entries = data[key];
      $('#combination').html(entries);
      $('input[name=Combination_Id]').val(key);
    });
  });
});
