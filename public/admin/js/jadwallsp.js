function geturl(modal, iframe, url) {
  // alert(url);
  // data-toggle="modal" data-target="#exampleModal"
  $("#" + modal).modal();
  document.getElementById(iframe).src = url;
  console.log(url);

  $(".spinner-border").css("display", "inline-block");
  setInterval(function () {
    $(".spinner-border").css("display", "none");
  }, 3000);
  var urldownload = url.replace("ScheduleLsp", "Download");
  document.getElementById("urldata").href = urldownload;
}
function modal(modal, iframe, url) {
  // alert(url);
  // data-toggle="modal" data-target="#exampleModal"
  $("#" + modal).modal();

  $(".spinner-border").css("display", "inline-block");
  setInterval(function () {
    $(".spinner-border").css("display", "none");
  }, 3000);
}
