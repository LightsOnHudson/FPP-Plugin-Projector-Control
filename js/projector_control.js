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
