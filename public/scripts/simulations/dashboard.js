var session = null;
var statuses = {};
var chosenSimulations = [];
var servers = {};
var simulationToServer = {};

var connection = new autobahn.Connection({
  url: "ws://www.numa.oan:8081/ws",
  realm: "realm1"
});

connection.onopen = function (newSession, details) {
  session = newSession;
  session.subscribe('com.gosmartsimulation.announce', onAnnounce)
  session.subscribe('com.gosmartsimulation.identify', onIdentify)
  session.subscribe('com.gosmartsimulation.status', onStatus)
  session.subscribe('com.gosmartsimulation.complete', onComplete)
  session.subscribe('com.gosmartsimulation.fail', onFail)
  requestIdentify();
};

connection.onclose = function (reason, details) {
};

function setProgress(tr, percentage)
{
  var width = 0;
  if (!isNaN(parseFloat(percentage)))
  {
    percentage = Math.round(percentage * 10) / 10;
    width = percentage;
    percentage = percentage + '%';
  }

  if (percentage === undefined)
  {
    tr.find('td[name=simulation-server-progress] span').html('');
    tr.find('td[name=simulation-server-progress] div').css('width', '0px');
  }
  else
  {
    tr.find('td[name=simulation-server-progress] span').html(percentage);
    tr.find('td[name=simulation-server-progress] div').css('width', width + 'px');
  }
}

function chooseServer()
{
  var serverChoice = $('input[name=server-choice]:checked').val();
  if (!serverChoice)
    return freestServer();
  return serverChoice;
}

function freestServer()
{
  var maxScore = null, maxServer = null;
  for (server in servers)
    if (maxServer === null || servers[server].score > maxScore)
    {
      maxServer = server;
      maxScore = servers[server].score;
    }

  return maxServer;
}

function onFail(args, skip_identify) {
  var id = args[0].toUpperCase();
  var stat = args[1];
  var directory = args[2];
  var timestamp = args[3];
  var validation = args[4];
  var serverId = args[5];

  if (!(id in statuses))
    statuses[id] = [];

  if (statuses[id][0] !== 'fail' && !skip_identify)
    requestIdentify();

  statuses[id].push(['fail', stat, directory, timestamp, validation, serverId]);
  reshowStatus(id);
}

function onComplete(args, skip_identify) {
  var id = args[0].toUpperCase();
  var stat = args[1];
  var directory = args[2];
  var timestamp = args[3];
  var validation = args[4];
  var serverId = args[5];

  if (!(id in statuses))
    statuses[id] = [];

  if (statuses[id][0] !== 'complete' && !skip_identify)
    requestIdentify();

  simulations[id].complete = true;
  statuses[id].push(['complete', stat, directory, timestamp, validation, serverId]);
  reshowStatus(id);
}

function showComplete(id, validation) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'hidden');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'green');
  simulation.find('div[name=simulation-server-message]').html("Success");

  var validationString;
  if (validation !== undefined) {
    var results = $.parseJSON(validation);
    if (results) {
      var rVol = parseFloat(results['ReferenceVolume']),
          iVol = parseFloat(results['IntersectionVolume']),
          oVol = parseFloat(results['VolumetricOverlap']),
          avAbsErr = parseFloat(results['AverageAbsoluteError']);

      validationString = " \
        <math display=\"block\"> \
        <mrow> \
          <mi>α</mi> \
          <mo>=</mo> \
          <mn>" + parseFloat(avAbsErr).toPrecision(3) + "</mn> \
        </mrow> \
        <mrow> \
          <msub> \
            <mi>ϕ</mi> \
            <mi>S</mi> \
          </msub> \
          <mo>=</mo> \
          <mn>" + (iVol / rVol).toPrecision(3) + "</mn> \
        </mrow> \
        <mrow> \
          <mi>ϕ</mi> \
          <mo>=</mo> \
          <mn>" + parseFloat(oVol).toPrecision(3) + "</mn> \
        </mrow> \
        </math> \
      ";
    }
  }

  setProgress(simulation, validationString);
}

function showError(id, res) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'visible');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'red');
  if (res)
  {
    var message = $('<div></div>').text(res.message).html();
    simulation.find('div[name=simulation-server-message]').html('[' + res.id + ':' + res.code + ']<br/><pre name="simulation-server-message-block">' + message + '</pre>');
  }
  setProgress(simulation);
}

function showDatabaseRequest(id, res) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'hidden');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'brown');
  simulation.find('div[name=simulation-server-message]').html(res);
  setProgress(simulation);
}

function getSimulationLocation(id) {
  var location;
  getLatestStatus(id); //Sorts
  if (id in statuses)
    for (s in statuses[id])
      if (statuses[id][s][2])
        location = statuses[id][s][2];
  return location;
}

function showProperties(id) {
  var simulation = $('#' + id);
  var location = getSimulationLocation(id);

  if (simulation.length == 0)
    return;

  if (location)
  {
    simulation.find('div[name=simulation-server-message]').prop('title', location);
    simulation.find('span[name=location]').html(location);
    simulation.find('.location').css('visibility', 'visible');
  }
  else
  {
    simulation.find('div[name=simulation-server-message]').prop('title', '');
    simulation.find('span[name=location]').html('');
    simulation.find('.location').css('visibility', 'hidden');
  }
}

function pickSimulation(id) {
  var index = chosenSimulations.indexOf(id);
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  if (index > -1) {
    chosenSimulations.splice(index, 1);
    simulation.css('background-color', 'inherit');
  }
  else {
    chosenSimulations.push(id);
    simulation.css('background-color', '#a66');
  }

  if (chosenSimulations.length == 2)
    $('#diffLink').removeClass('disabled')
  else
    $('#diffLink').addClass('disabled')
}

function showMessage(id, res) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'hidden');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'yellow');
  simulation.find('div[name=simulation-server-message]').html(res);
  setProgress(simulation);
}

function rebuildSimulation(id) {
  var rebuildLink = $('#' + id + ' a[name=rebuild]').attr('href');

  $.get(rebuildLink, [], function (data) {
    alert(data.msg);
    regenerateBoard();
  }).fail(function (jqXHR, textStatus, errorThrown) { showError(id, errorThrown) });
}

function duplicateSimulation(id) {
  var dupLink = $('#' + id + ' a[name=duplicate]').attr('href');

  if (session)
  {
    var sE = function(err) { showError(id, err.args[0]); };
    var newName = prompt("Name for new simulation", "");

    $.get(dupLink, {caption: newName}, function (simulation) {
      simulations[simulation.Id] = simulation;
      regenerateBoard();
    });
  }
  else
  {
    console.error('No WAMP session available for simulation server connection');
  }
}

function diffSimulations(idThis, idThat) {
  var xmlThisLink = $('#' + idThis + ' a[name=xml-link]').attr('href');
  var xmlThatLink = $('#' + idThat + ' a[name=xml-link]').attr('href');

  $.when(
      $.get(xmlThisLink),
      $.get(xmlThatLink)
  ).done(function (responseThis, responseThat) {
    var xmlThis = (new XMLSerializer()).serializeToString(responseThis[0]);
    var xmlThat = (new XMLSerializer()).serializeToString(responseThat[0]);
    var s = chooseServer();
    session.call('com.gosmartsimulation.' + s + '.compare', [xmlThis, xmlThat]).then(function (difflines) {
      var w = window.open();
      var body = $(w.document.body);
      var pre = $("<pre></pre>");
      pre.appendTo(body);
      for (line in difflines) {
        pre.append(difflines[line] + "\n");
      }
    });
  });
}

function startSimulation(id) {
  var xmlLink = $('#' + id + ' a[name=xml-link]').attr('href');

  if (session)
  {
    var sE = function(err) { showError(id, err.args[0]); };

    showDatabaseRequest(id, 'Retrieving XML');
    $.get(xmlLink, [], function (xml) {
      var s = chooseServer();
      session.call('com.gosmartsimulation.' + s + '.init', [id]).then(function (res) {
        statuses[id] = [];
        showMessage(id, "Initiated");
        var xmlString = (new XMLSerializer()).serializeToString(xml);
        session.call('com.gosmartsimulation.' + s + '.update_settings_xml', [id, xmlString]).then(function (res) {
          showMessage(id, "XML set");
          session.call('com.gosmartsimulation.' + s + '.finalize', [id, '.']).then(function (res) {
            showMessage(id, "Settings finalized");
            session.call('com.gosmartsimulation.' + s + '.start', [id]).then(function (res) {
              showMessage(id, "Started...");
              session.call('com.gosmartsimulation.' + s + '.properties', [id]).then(function (res) {
                showProperties(id);
              }, sE);
            }, sE); }, sE); }, sE); }, sE);
    }, 'xml');
  }
  else
  {
    console.error('No WAMP session available for simulation server connection');
  }
}

function regenerateBoard() {
  var table = $('.simulations-table');
  table.empty();

  var patientList = rebuildPatientList();
  var sortedPatients = patientList.sortedPatients;
  var patients = patientList.patients;
  sortedPatients.forEach(function (patientId)
  {
    var patient = patients[patientId];
    var internalTable = '<tr><td><div><h2 id="' + patientId + '" title="' + patientId + '">' + patient.alias + ' : ' + patient.clinician + '</h2>';
    if (patient.description)
      internalTable += '<h4>' + patientId + ' | ' + patient.description + '</h4>';
    internalTable += '<a href="#">[top]</a>';
    internalTable += '<table id="patient-' + patientId + '"></table></div></td></tr>';
    table.append(internalTable);
    var patientTable = $('#patient-' + patientId);
    var tree = _makeSimulationsTree(patient.simulations);
    for (var i = 0; i < patient.simulations.length; i++)
    {
      var simulation = tree[patient.simulations[i].Id];
      if (simulation)
        _fullRow(simulation, patientTable);
    }
  });

}

function rebuildPatientList()
{
  var patients = {"None": {"alias": "[none]", "simulations": [], "description": "Patients with no alias"}};

  var sortedSimulations = Object.keys(simulations).sort(function (a, b) {
    if (!simulations[a].modality)
      return simulations[b].modality ? -1 : 0;
    if (!simulations[b].modality)
      return 1;
    var strcmp = simulations[a].modality.Id.localeCompare(simulations[b].modality.Id);
    if (strcmp != 0)
      return strcmp;
    if (!simulations[a].creationDate)
      return simulations[b].creationDate ? -1 : 0;
    if (!simulations[b].creationDate)
      return 1;
    return simulations[a].creationDate.localeCompare(simulations[b].creationDate);
  });

  var modalitiesByOrgan = {};
  var modalitiesByPatient = {};

  sortedSimulations.forEach(function (Id)
  {
    var simulation = simulations[Id];

    if (!simulation.patient)
    {
      patients["None"].simulations.push(simulation);
      return;
    }

    var patient = simulation.patient;

    if (!(simulation.Patient_Id in patients))
    {
      patients[simulation.Patient_Id] = {
        'alias': simulation.PatientAlias + ' [' + simulation.modality.Name + '|' + simulation.contextName + ']',
        'description': simulation.PatientDescription,
        'simulations': [],
        'modality': simulation.modality.Name,
        'context': simulation.contextName,
        'clinician': simulation.ClinicianUserName
       };
    }
    if (simulation.hasSegmentedLesion)
      patients[simulation.Patient_Id].lesion = true;
    if (simulation.complete)
      patients[simulation.Patient_Id].someComplete = true;
    patients[simulation.Patient_Id].simulations.push(simulation);
  });

  var sortedPatients = Object.keys(patients).sort(function (a, b) {
    return patients[a].alias.localeCompare(patients[b].alias);
  });

  var patientList = '<ul>';
  sortedPatients.forEach(function (patientId)
  {
    var patient = patients[patientId];
    patientList += '<li><a href="#' + patientId + '" class="patient-link patient-' + patient.modality;
    if (patient.lesion)
    {
      patientList += ' has-segmented-lesion';
      if (!patient.someComplete)
        patientList += ' no-complete';
    }
    patientList += '">' + patient.alias + '</a></li> |\n';

  });

  $('#patientList').html(patientList);

  return {'patients': patients, 'sortedPatients': sortedPatients};
}

function _makeSimulationsTree(simulations)
{
  var tree = {};
  var simulationMap = {};
  simulations.map(function (s) { simulationMap[s.Id] = s; });
  var simulationIds = Object.keys(simulationMap);
  simulationIds.map(function (id) {
    if (!simulationMap[id])
      return;
    var simulation = simulationMap[id];
    var original_id = simulation.Original_Id;
    var parent_id = simulation.Parent_Id;
    if (!original_id && !parent_id)
    {
      tree[id] = simulation;
    }

    if (original_id in simulationMap)
    {
      if (simulationMap[original_id].replicas === undefined)
        simulationMap[original_id].replicas = {};
      simulationMap[original_id].replicas[id] = simulation;
    }
    else if (parent_id in simulationMap)
    {
      if (simulationMap[parent_id].children === undefined)
        simulationMap[parent_id].children = {};
      simulationMap[parent_id].children[id] = simulation;
    }
  });
  return tree;
}

function _childRow(simulation, patientTable, depth)
{
      var Id = simulation.Id;
      var classes = 'simulation';
      if (simulation.isDeleted)
        classes += ' deleted';
      if (simulation.Parent_Id)
        classes += ' simulation-consecutive';
      patientTable.append('<tr id="' + Id + '" class="' + classes + '">' + "\n");
      var tr = patientTable.find('#' + Id);
      var button = '<td name="simulation-server-status">';
      button += '<a href="#" name="start">&#9658;</a>';
      button += '</td>';
      tr.append(button);

      tools =
          '<a href="' + duplicateLink(Id) + '" title="Duplicate" name="duplicate">&#9842;</a>'
          + '<a href="#" title="Pick" name="pick">&#9935;</a>'
          + '<a href="' + rebuildLink(Id) + '" title="Rebuild" name="rebuild">&#x1f3ed;</a>'
          + '<a href="#" title="Revalidate" name="revalidate">&#x1f453;</a>';

      if (simulation.hasSegmentedLesion)
          tools += '<a href="' + segmentedLesionLink(Id) + '" title="Segmented lesion" target="segmentedLesion" name="segmentedLesion">&#x1f359;</a>';

      tr.append('<td name="simulation-tools">'
          + tools
          + '</td>');
      if (!simulation.Original_Id && simulation.Parent_Id)
        tr.append('<td class="simulation-child"></td>');
      else
        tr.append('<td></td>');
      var shortId = String(Id).substr(0, 6);
      tr.append('<td name="name" style="padding-left:' + (20 * depth) + 'px">'
          + simulation.asHtml + ' [' + simulation.creationDate + '] <span style="color: #aaa">' + shortId + '...</span><br/>'
          + ' <span class="location">[ <span name="location"></span> ]</span></td>');
      if (simulation.interactive === false)
      {
        tr.append('<td></td>');
      }
      else
      {
        tr.append('<td>[<span style="font-size: xx-small"><a href="' + editLink(Id) + '">e</a>'
          + '<a href="' + xmlLink(Id) + '" name="xml-link">X</a><a href="' + htmlLink(Id) + '">H</a></span>]</td>');
      }
      tr.append('<td name="simulation-server-progress"><span name="progress-number"></span><div name="progress-bar"></div></td>');
      tr.append('<td id="simulation-' + Id + '-parameter" class="combination-parameters"></td>');
      tr.append('<td name="simulation-server-details"><div name="simulation-server-validation"></div><br/><div name="simulation-server-timing"></div><br/><div name="simulation-server-message"></div></td>');
      tr.find('a[name=revalidate]').unbind("click");
      tr.find('a[name=revalidate]').bind("click", handleRevalidate);
      tr.find('a[name=revalidate]').hide();
      tr.find('a[name=pick]').unbind("click");
      tr.find('a[name=pick]').bind("click", handlePick);
      tr.find('a[name=start]').unbind("click");
      tr.find('a[name=start]').bind("click", handleStart);
      tr.find('a[name=rebuild]').unbind("click");
      tr.find('a[name=rebuild]').bind("click", handleRebuild);
      tr.find('a[name=duplicate]').unbind("click");
      tr.find('a[name=duplicate]').bind("click", handleDuplicate);
      reshowStatus(Id);
      showProperties(Id);

      for (replica in simulation.replicas)
        _childRow(simulation.replicas[replica], patientTable, depth + 1);
      for (child in simulation.children)
        _childRow(simulation.children[child], patientTable, depth);
}

function _fullRow(simulation, patientTable)
{
      var Id = simulation.Id;
      var classes = 'simulation';
      if (simulation.isDeleted)
        classes += ' deleted';
      patientTable.append('<tr id="' + Id + '" class="' + classes + '">' + "\n");
      var tr = patientTable.find('#' + Id);
      var button = '<td name="simulation-server-status">';
      button += '<a href="#" name="start">&#9658;</a>';
      button += '</td>';
      tr.append(button);

      tools =
          '<a href="' + duplicateLink(Id) + '" title="Duplicate" name="duplicate">&#9842;</a>'
          + '<a href="#" title="Pick" name="pick">&#9935;</a>'
          + '<a href="' + rebuildLink(Id) + '" title="Rebuild" name="rebuild">&#x1f3ed;</a>'
          + '<a href="#" title="Revalidate" name="revalidate">&#x1f453;</a>';

      if (simulation.hasSegmentedLesion)
          tools += '<a href="' + segmentedLesionLink(Id) + '" title="Segmented lesion" target="segmentedLesion" name="segmentedLesion">&#x1f359;</a>';

      tr.append('<td name="simulation-tools">'
          + tools
          + '</td>');
      if (simulation.Parent_Id)
        tr.append('<td class="simulation-child"></td>');
      else if (simulation.modality)
        tr.append('<td class="modality-indicator modality-' + simulation.modality.Name + '"></td>');
      else
        tr.append('<td></td>');
      var shortId = String(Id).substr(0, 6);
      var simulationPatient = '(none)';
      if (simulation.patient)
      {
        simulationPatient = '<span class="patient" title="' + simulation.patient.Description + '">' + simulation.patient.Alias + ' (' + simulation.contextName + ')</span>';
      }
      tr.append('<td name="name">' + simulationPatient + '<br/>'
          + simulation.asHtml + ' [' + simulation.creationDate + '] <span style="color: #aaa">' + shortId + '...</span><br/><span style="font-size:xx-small">'
          + simulation.asString + '</span>' + ' <span class="location">[ <span name="location"></span> ]</span></td>');
      if (simulation.interactive === false)
      {
        tr.append('<td></td>');
      }
      else
      {
        tr.append('<td>[<span style="font-size: xx-small"><a href="' + editLink(Id) + '">e</a>'
          + '<a href="' + xmlLink(Id) + '" name="xml-link">X</a><a href="' + htmlLink(Id) + '">H</a></span>]</td>');
      }
      tr.append('<td name="simulation-server-progress"><span name="progress-number"></span><div name="progress-bar"></div></td>');
      tr.append('<td id="simulation-' + Id + '-parameter" class="combination-parameters"></td>');
      tr.append('<td name="simulation-server-details"><div name="simulation-server-validation"></div><br/><div name="simulation-server-timing"></div><br/><div name="simulation-server-message"></div></td>');
      tr.find('a[name=revalidate]').unbind("click");
      tr.find('a[name=revalidate]').bind("click", handleRevalidate);
      tr.find('a[name=revalidate]').hide();
      tr.find('a[name=pick]').unbind("click");
      tr.find('a[name=pick]').bind("click", handlePick);
      tr.find('a[name=start]').unbind("click");
      tr.find('a[name=start]').bind("click", handleStart);
      tr.find('a[name=rebuild]').unbind("click");
      tr.find('a[name=rebuild]').bind("click", handleRebuild);
      tr.find('a[name=duplicate]').unbind("click");
      tr.find('a[name=duplicate]').bind("click", handleDuplicate);
      reshowStatus(Id);
      showProperties(Id);

      for (replica in simulation.replicas)
        _childRow(simulation.replicas[replica], patientTable, 1);
      for (child in simulation.children)
        _fullRow(simulation.children[child], patientTable);
}

function makeListForValidation(id)
{
  var latestStatus = getLatestStatus(id);
  var condition = latestStatus[0];
  var detail = latestStatus[1];
  var directory = latestStatus[2];
  var timestamp = latestStatus[3];
  var validation = latestStatus[4];
  var results = $.parseJSON(validation);
  var simulation = simulations[id];
  var date = new Date(timestamp * 1000);
  var serverId = simulationToServer[id];
  var server = servers[serverId];
  var date_string = date.toUTCString();

  var html = "<dl class='validation'>";
  html += "<dt>Patient</dt><dd><em>" + simulation.patient.Alias + "</em>: " + simulation.patient.Description + "</dd>";
  html += "<dt>PatID</dt><dd>" + simulation.Patient_Id + "</dd>";
  html += "<dt>Organ</dt><dd>" + simulation.contextName + "</dd>";
  html += "<dt>ID</dt><dd>" + id + "</dd>";
  html += "<dt>Directory</dt><dd>" + directory + "</dd>";
  html += "<dt>Timestamp</dt><dd>" + date_string + "</dd>";
  html += "<dt>Server</dt><dd>" + server.id + " (" + server.host + ")</dd>";
  for (term in results)
    html += "<dt>" + term + "</dt><dd>" + results[term] + "</dd>";
  html += "</dl>";

  return html;
}

function getLatestStatus(id)
{
  if (!(id in statuses))
    return;

  statuses[id].sort(function (s, t) { return s[3] - t[3]; });
  return statuses[id][statuses[id].length - 1];
}

function reshowStatus(id)
{
  if (!(id in statuses))
    return;

  var latestStatus = getLatestStatus(id);
  var condition = latestStatus[0];
  var detail = latestStatus[1];
  var directory = latestStatus[2];
  var timestamp = latestStatus[3];
  var validation = latestStatus[4];
  var serverId = latestStatus[5];

  if (condition == 'complete')
    showComplete(id, validation);
  else if (condition == 'fail')
    showError(id, detail);
  else if (condition == 'status')
    showStatus(id, detail[0], detail[1]);

  if (validation)
  {
    var tr = $('#' + id);
    tr.find('div[name=simulation-server-validation]').html(makeListForValidation(id));
    tr.find('a[name=revalidate]').show();
  }

  if (timestamp !== undefined)
  {
    var date = new Date(timestamp * 1000);
    var date_string = "";
    if (statuses[id].length > 1)
    {
      var firstStatus = statuses[id][0];
      if (firstStatus[3] !== undefined)
      {
        var firstDate = new Date(firstStatus[3] * 1000);
        date_string += firstDate.toUTCString() + ' -> ';
      }
    }
    date_string += date.toUTCString();
    var tr = $('#' + id);
    tr.find('div[name=simulation-server-timing]').html(date_string);
  }
}

function onStatus(args) {
  var id = args[0].toUpperCase();
  var stat = args[1];
  var directory = args[2];
  var timestamp = args[3];
  var validation = args[4];
  var serverId = args[5];

  if (!(id in statuses))
    statuses[id] = [];

  statuses[id].push(['status', stat, directory, timestamp, validation, serverId]);
  reshowStatus(id);
}

function showStatus(id, percentage, statusMessage) {
  var tr = $('#' + id);
  if (tr.length > 0)
  {
    tr.find('td[name=simulation-server-status] a').css('visibility', 'hidden');
    tr.find('td[name=simulation-server-status]').css('background-color', 'yellow');
    tr.find('div[name=simulation-server-message]').html(statusMessage.message);
    setProgress(tr, percentage);
  }
}

function requestIdentify() {
  session.publish('com.gosmartsimulation.request_identify', [])
  rebuildPatientList();
}

function updateServers() {
  var selected = null;
  var selected_widget = $('input[name=server-choice]:checked');
  if (selected_widget)
    selected = selected_widget.val();

  if (!selected)
    selected = preferred_server;

  var tbody = $('#servers-table');
  tbody.empty();
  tbody.append('<tr><td><input type="radio" name="server-choice" value="" checked /></td><td>[best score]</td><td></td><td></td></tr>');
  for (server in servers)
  {
    var score = servers[server].score;
    var hostname = servers[server].host;
    tbody.append('<tr><td><input type="radio" name="server-choice" value="' + server + '" /><td>' + server + '</td><td>' + hostname + '</td><td>' + score + '</td></tr>');
  }
  if (selected)
    $('input[name=server-choice]').filter('[value=' + selected + ']').prop('checked', true);
}

function onIdentify(args) {
  var serverId = args[0].toLowerCase();
  var hostname = args[1];
  var score = args[2];
  var requestAnnounce = !(serverId in servers);
  servers[serverId] = {id: serverId, host: hostname, score: score};
  updateServers();

  if (requestAnnounce)
    session.publish('com.gosmartsimulation.request_announce', [])
}

function onAnnounce(args) {
  var tr;

  //if ($('#' + args[0]).length == 0)
  //{
  //  simulations[args[0]] = {"Id": args[0], "asHtml": "[-- Unknown --]", "asString": "", "interactive": false};
  //}

  var serverId = args[0].toLowerCase();
  var guid = args[1];
  var stat = args[2];
  var directory = args[3];
  var timestamp = args[4];
  var validation = args[5];

  if (!(serverId in servers))
    alert("Unidentified server announcing");

  tr = $('#' + guid);
  simulationToServer[guid] = serverId;

  if (stat)
  {
    var state = stat[1];
    if (state.code == 'SUCCESS')
    {
      onComplete([guid, state, directory, timestamp, validation, serverId], true);
    }
    else if (state.code == 'IN_PROGRESS')
    {
      onStatus([guid, stat, directory, timestamp, validation, serverId], true);
    }
    else
    {
      onFail([guid, state, directory, timestamp, validation, serverId], true);
    }
  }

  showProperties(args[1]);

  rebuildPatientList();
};

function handleRebuild(e) {
  e.preventDefault();
  var simulation = $(e.target).closest('tr').attr('id');
  rebuildSimulation(simulation);
}

function handleDuplicate(e) {
  e.preventDefault();
  var simulation = $(e.target).closest('tr').attr('id');
  duplicateSimulation(simulation);
}

function handlePick(e) {
  e.preventDefault();
  var simulation = $(e.target).closest('tr').attr('id');
  pickSimulation(simulation);
}

function handleStart(e) {
  e.preventDefault();
  var simulation = $(e.target).closest('tr').attr('id');
  startSimulation(simulation);
}

function handleDiff(e) {
  e.preventDefault();
  if (chosenSimulations.length == 2)
    diffSimulations(chosenSimulations[0], chosenSimulations[1]);
  else
    alert("Must pick exactly two simulations");
}

function handleUpdatePreferredServer(e) {
  e.preventDefault();
  var url = $(e.target).attr('href');
  var choice = $('input[name=server-choice]:checked').val();
  $.get(url, {preferred_server: choice}, function (data) {
    if (data.success) {
      $(e.target).fadeOut(100).fadeIn('slow');
      preferred_server = choice;
      $('input[name=server-choice]').filter('[value=' + choice + ']').prop('checked', true);
    }
  });
}

function handleRevalidate(e) {
  e.preventDefault();
  var simulationId = $(e.target).closest('tr').attr('id');
  var latestStatus = getLatestStatus(simulationId);
  var s = latestStatus[5];
  session.call('com.gosmartsimulation.' + s + '.tmp_validation', [simulationId, latestStatus[2]]).then(function (res) {
    alert('Re-read validation');

  });
}

$(function () {
  $('a[name=updatePreferredServer]').click(handleUpdatePreferredServer);
  connection.open();
  $('#diffLink').click(handleDiff);
  regenerateBoard();
});
