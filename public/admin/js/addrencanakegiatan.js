$("#jenis_tempat").change(function () {
  $.ajax({
    url: "idtempat",
    method: "GET",
    data: { kode: $(this).val() },
    dataType: "json",
    beforeSend: function (D) {
      // console.log(D);
    },
    afterSend: function (selesai) {
      console.log(selesai);
    },
    success: function (response) {
      $("#id_tempat").html("");
      response.forEach((element) => {
        console.log(element);
        if (element.skegiatan == 1) {
          $("#id_tempat").append(
            '<option style="color:#bdc2c7" disabled value="' +
              element.kode +
              '">' +
              element.title_name +
              "</option>"
          );
        } else {
          0;
          $("#id_tempat").append(
            '<option value="' +
              element.kode +
              '">' +
              element.title_name +
              "</option>"
          );
        }
      });
    },
  });
});
