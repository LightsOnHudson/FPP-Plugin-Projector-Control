$(document).ready(async () => {
  await init();
});

async function checkPluginUpdates() {
  await FPPPost(
    "/api/plugin/FPP-Plugin-Projector-Control/updates",
    {},
    (data) => {
      if (data?.updatesAvailable === 1) {
        $("#updatesAvailable").html(
          '<h4 style="color:red;">A Plugin Update is Available</h4>' +
          '<button class="buttons btn-success" onclick="UpgradePlugin(&quot;FPP-Plugin-Projector-Control&quot;)"><i class="far fa-arrow-alt-circle-down"></i> Update Now</button>'
        );
      }
    }
  );
}

async function FPPPost(url, data, successCallback) {
  await $.ajax({
    url,
    type: "POST",
    contentType: "application/json",
    dataType: "json",
    data,
    async: true,
    success: (data, statusText, xhr) => {
      successCallback(data, statusText, xhr);
    },
  });
}

async function init() {
  await checkPluginUpdates();
}
function validateIP(){
	var ipAddress = document.getElementById("IP").value;
    var ipRegex = /^(?:|(?:(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)))$/;

    if (ipRegex.test(ipAddress)) {
        document.getElementById('IP_Warning').style.display = "none";        
    } else {
        document.getElementById('IP_Warning').style.display = "block";
    }
}

function validatePort(){
	var ipAddress = document.getElementById("PORT").value;
	var ipRegex = /^(?:|(?:(?:\d+)))$/;
	if (ipRegex.test(ipAddress)) {
        document.getElementById('Port_Warning').style.display = "none";        
    } else {
        document.getElementById('Port_Warning').style.display = "block";
    }
}

