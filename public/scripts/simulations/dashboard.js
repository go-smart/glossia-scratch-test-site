var session = null;

var connection = new autobahn.Connection({
  url: "ws://www.numa.oan:8081/ws",
  realm: "realm1"
});

connection.onopen = function (newSession, details) {
  session = newSession;
  session.subscribe('com.gosmartsimulation.announce', onAnnounce)
  session.subscribe('com.gosmartsimulation.status', onStatus)
  session.publish('com.gosmartsimulation.request_announce', [])
  session.subscribe('com.gosmartsimulation.complete', onComplete)
  session.subscribe('com.gosmartsimulation.fail', onFail)
};

connection.onclose = function (reason, details) {
};

function setProgress(tr, percentage=null)
{
  if (!isNaN(parseFloat(percentage)))
  {
    percentage = Math.round(percentage * 10) / 10;
    tr.find('td[name=simulation-server-progress] span').html(percentage + '%');
    tr.find('td[name=simulation-server-progress] div').css('width', percentage + 'px');
  }
  else
  {
    tr.find('td[name=simulation-server-progress] span').html('');
    tr.find('td[name=simulation-server-progress] div').css('width', '0px');
  }
}

function onFail(args) {
  var simulation = $('#' + args[0]);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'visible');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'red');
  simulation.find('td[name=simulation-server-message]').html(args[1]);
  setProgress(simulation);
}

function onComplete(args) {
  var simulation = $('#' + args[0]);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'visible');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'green');
  simulation.find('td[name=simulation-server-message]').html("Success");
  setProgress(simulation);
}

function showError(id, res) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'visible');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'red');
  simulation.find('td[name=simulation-server-message]').html(res);
  setProgress(simulation);
}

function showDatabaseRequest(id, res) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'hidden');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'brown');
  simulation.find('td[name=simulation-server-message]').html(res);
  setProgress(simulation);
}

function showProperties(id, res) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-message]').prop('title', res.location);
}

function showMessage(id, res) {
  var simulation = $('#' + id);
  if (simulation.length == 0)
    return;

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'hidden');
  simulation.find('td[name=simulation-server-status]').css('background-color', 'yellow');
  simulation.find('td[name=simulation-server-message]').html(res);
  setProgress(simulation);
}

function duplicateSimulation(id) {
  var dupLink = $('#' + id + ' a[name=duplicate]').attr('href');

  if (session)
  {
    var sE = function(err) { showError(id, err.args[0]); };

    $.get(dupLink, [], function (simulation) {
      simulations[simulation.Id] = simulation;
      regenerateBoard();
    });
  }
  else
  {
    console.error('No WAMP session available for simulation server connection');
  }
}

function startSimulation(id) {
  var xmlLink = $('#' + id + ' a[name=xml-link]').attr('href');

  if (session)
  {
    var sE = function(err) { showError(id, err.args[0]); };

    showDatabaseRequest(id, 'Retrieving XML');
    $.get(xmlLink, [], function (xml) {
      session.call('com.gosmartsimulation.init', [id]).then(function (res) {
        showMessage(id, "Initiated");
        var xmlString = (new XMLSerializer()).serializeToString(xml);
        session.call('com.gosmartsimulation.update_settings_xml', [id, xmlString]).then(function (res) {
          showMessage(id, "XML set");
          session.call('com.gosmartsimulation.finalize', [id, '.']).then(function (res) {
            showMessage(id, "Settings finalized");
            session.call('com.gosmartsimulation.start', [id]).then(function (res) {
              showMessage(id, "Started...");
              session.call('com.gosmartsimulation.properties', [id]).then(function (res) {
                showProperties(id, res);
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
  var clinicians = {"None": {"simulations": [], "UserName": "[none]"}};
  for (Id in simulations)
  {
    var simulation = simulations[Id];

    if (!simulation.clinician)
    {
      clinicians["None"].simulations.push(simulation);
      continue;
    }

    var clinician = simulation.clinician;

    if (!(simulation.clinician.Id in clinicians))
    {
      clinicians[simulation.clinician.Id] = clinician;
      clinician.simulations = [];
    }
    clinicians[simulation.clinician.Id].simulations.push(simulation);
  }

  for (clinicianId in clinicians)
  {
    var clinician = clinicians[clinicianId];

    table.append('<tr><td><div><h2>' + clinician.UserName + '</h2><table id="clinician-' + clinicianId + '"></table></div></td></tr>');
    var clinicianTable = $('#clinician-' + clinicianId);
    for (var i = 0; i < clinician.simulations.length; i++)
    {
      var simulation = clinician.simulations[i];
      var Id = simulation.Id;
      clinicianTable.append('<tr id="' + Id + '" class="simulations">');
      var tr = clinicianTable.find('#' + Id);
      tr.append('<td name="simulation-server-status"><a href="#" name="start">&#9658;</a></td>');
      tr.append('<td><a href="' + duplicateLink(Id) + '" name="duplicate">&#9842;</a></td>');
      tr.append('<td name="name">' + simulation.asHtml + '<br/><span style="font-size:xx-small">' + simulation.asString + '</span></td>');
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
      tr.append('<td name="simulation-server-message"></td>');
      tr.find('a[name=start]').click(handleStart);
      tr.find('a[name=duplicate]').click(handleDuplicate);
    }
  }
}

function onStatus(args) {
  var id = args[0];
  var percentage = args[1];
  var statusMessage = args[2];

  var tr = $('#' + args[0]);
  if (tr.length > 0)
  {
    tr.find('td[name=simulation-server-message]').html(statusMessage);
    setProgress(tr, percentage);
  }
}

function onAnnounce(args) {
  var tr;

  if ($('#' + args[0]).length == 0)
  {
    simulations[args[0]] = {"Id": args[0], "asHtml": "[-- Unknown --]", "asString": "", "interactive": false};
  }

  tr = $('#' + args[0]);

  simulation.find('td[name=simulation-server-status] a').css('visibility', 'hidden');
  tr.find('td[name=simulation-server-status]').css('background-color', 'yellow');

  if (args[1])
  {
    if (args[1][0])
      onComplete(args[0]);
    else
      onFail(args[0], args[1][1]);
  }
};

function handleDuplicate(e) {
  e.preventDefault();
  var simulation = $(e.target).closest('tr').attr('id');
  duplicateSimulation(simulation);
}

function handleStart(e) {
  e.preventDefault();
  var simulation = $(e.target).closest('tr').attr('id');
  startSimulation(simulation);
}

$(function () {
  connection.open();
  regenerateBoard();
  $('.simulations-table a[name=start]').click(handleStart);
  $('.simulations-table a[name=duplicate]').click(handleDuplicate);
});
