$(document).ready(function () {
  $("#example").DataTable();
});
var kegiatan = "";
function tambahdatahasil(var1) {
  console.log(var1);
  kegiatan = var1;
  $("input[name='id_kegiatan']").val(var1);
  $("#Modeldatadialog").modal("show");
  $("#kegiatan_asesi_view").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/asesidata/" + var1,
    },
    columns: [
      { data: "nomorku" },
      { data: "id_izin" },
      { data: "telepon" },
      { data: "nama" },
    ],
  });

  $("#skema_view").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/skemadata/" + var1,
    },
    columns: [{ data: "nomorku" }, { data: "jabatan_kerja" }],
  });
}

$("#asesor-tab").click(function () {
  $("#kegiatan_asesor_view").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/asesordata/" + kegiatan,
    },
    columns: [
      { data: "nomorku" },
      { data: "no_hp" },
      { data: "nama_asesor" },
      { data: "sertifikat_asesor_bnsp" },
    ],
  });
});

$("#koordinator-tab").click(function () {
  $("#kegiatan_koordinator_view").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/koordinatordata/" + kegiatan,
    },
    columns: [{ data: "nomorku" }, { data: "nama" }],
  });
});

$("#skema-tab").click(function () {
  $("#skema_view").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/skemadata/" + kegiatan,
    },
    columns: [{ data: "nomorku" }, { data: "jabatan_kerja" }],
  });
});

$("#timpemutus-tab").click(function () {
  console.log("tim pemutus klikable atau tidak");
  $("#kegiatan_timpemutus_view").DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/timpemutusdata/" + kegiatan,
    },
    columns: [{ data: "nomorku" }, { data: "nama_lengkap" }],
  });
});

$("#submit-asesi").click(function () {
  console.log(
    $("input[name='id_kegiatan']").val(),
    $("select[name='id_izin']").val()
  );
  $.ajax({
    url: "Rencanakegiatan/insertasesi",
    method: "post",
    dataType: "json",
    beforeSend: function () {
      $("#submit-asesi").attr("disabled", true);
    },
    complete: function () {
      $("#submit-asesi").attr("disabled", false);
      $("select[name='id_izin']").val("").trigger("change");
    },

    data: {
      id_kegiatan: $("input[name='id_kegiatan']").val(),
      id_izin: $("select[name='id_izin']").val(),
    },
    success: function (vvvx) {
      $("#kegiatan_asesi_view").DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
          url:
            "Rencanakegiatan/asesidata/" + $("input[name='id_kegiatan']").val(),
        },
        columns: [
          { data: "id_kegiatan" },
          { data: "id_izin" },
          { data: "telepon" },
          { data: "nama" },
        ],
      });
    },
  });

  return false;
});

$("#submit-koordinator").click(function () {
  console.log(
    $("input[name='id_kegiatan']").val(),
    $("select[name='koordinator_id']").val()
  );
  $.ajax({
    url: "Rencanakegiatan/insertkoordinator",
    method: "post",
    dataType: "json",
    beforeSend: function () {
      $("#submit-koordinator").attr("disabled", true);
    },
    complete: function () {
      $("#submit-koordinator").attr("disabled", false);
    },
    data: {
      id_kegiatan: $("input[name='id_kegiatan']").val(),
      koordinator_id: $("select[name='koordinator_id']").val(),
    },
    success: function (vvvx) {
      $("#kegiatan_koordinator_view").DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
          url: "Rencanakegiatan/koordinatordata/" + kegiatan,
        },
        columns: [{ data: "nomorku" }, { data: "nama" }],
      });
    },
  });

  return false;
});

$("#submit-skema").click(function () {
  console.log(
    $("input[name='id_kegiatan']").val(),
    $("select[name='id_skema']").val()
  );
  $.ajax({
    url: "Rencanakegiatan/insertskema",
    method: "post",
    dataType: "json",
    beforeSend: function () {
      $("#submit-skema").attr("disabled", true);
    },
    complete: function () {
      $("#submit-skema").attr("disabled", false);
      $("select[name='id_skema']").val("").trigger("change");
    },
    data: {
      kegiatan_id: $("input[name='id_kegiatan']").val(),
      jabatan_kerja_id: $("select[name='id_skema']").val(),
    },
    success: function (vvvx) {
      $("#skema_view").DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
          url:
            "Rencanakegiatan/skemadata/" + $("input[name='id_kegiatan']").val(),
        },
        columns: [{ data: "nomorku" }, { data: "jabatan_kerja" }],
      });
    },
  });

  return false;
});

$("#submit-asesor").click(function () {
  console.log(
    $("input[name='id_kegiatan']").val(),
    $("select[name='asesor_id']").val()
  );
  $.ajax({
    url: "Rencanakegiatan/insertasesor",
    method: "post",
    dataType: "json",
    beforeSend: function () {
      $("#submit-asesor").attr("disabled", true);
    },
    complete: function () {
      $("#submit-asesor").attr("disabled", false);
      $("select[name='asesor_id']").val("").trigger("change");
    },
    data: {
      id_kegiatan: $("input[name='id_kegiatan']").val(),
      asesor_id: $("select[name='asesor_id']").val(),
    },
    success: function (vvvx) {
      $("#kegiatan_asesor_view").DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
          url:
            "Rencanakegiatan/asesordata/" +
            $("input[name='id_kegiatan']").val(),
        },
        columns: [
          { data: "id_kegiatan" },
          { data: "no_hp" },
          { data: "nama_asesor" },
          { data: "sertifikat_asesor_bnsp" },
        ],
      });
    },
  });

  return false;
});

$("#submit-timpemutus").click(function () {
  console.log(
    $("input[name='id_kegiatan']").val(),
    $("select[name='tp_id']").val()
  );
  $.ajax({
    url: "Rencanakegiatan/inserttimpemutus",
    method: "post",
    dataType: "json",
    beforeSend: function () {
      $("#submit-timpemutus").attr("disabled", true);
    },
    complete: function () {
      $("#submit-timpemutus").attr("disabled", false);
      $("select[name='tp_id']").val("").trigger("change");
    },
    data: {
      id_kegiatan: $("input[name='id_kegiatan']").val(),
      tp_id: $("select[name='tp_id']").val(),
    },
    success: function (vvvx) {
      $("#kegiatan_timpemutus_view").DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
          url:
            "Rencanakegiatan/timpemutusdata/" +
            $("input[name='id_kegiatan']").val(),
        },
        columns: [{ data: "tp_id" }, { data: "nama_lengkap" }],
      });
    },
  });

  return false;
});

$(".js-data-example-ajax").select2({
  ajax: {
    url: "Rencanakegiatan/personalia/" + kegiatan,
    dataType: "json",
    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
    data: function (params) {
      var queryParameters = {
        term: params.term,
      };
      return queryParameters;
    },
    processResults: function (data) {
      return {
        results: $.map(data, function (item) {
          return {
            text: item.id_izin + " -> " + item.nama,
            id: item.id_izin,
          };
        }),
      };
    },
  },
});

$(".js-data-example-ajax-koordinator").select2({
  ajax: {
    url: "Rencanakegiatan/koordinator/" + kegiatan,
    dataType: "json",
    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
    data: function (params) {
      var queryParameters = {
        term: params.term,
      };
      return queryParameters;
    },
    processResults: function (data) {
      return {
        results: $.map(data, function (item) {
          return {
            text: item.id + " -> " + item.nama,
            id: item.id,
          };
        }),
      };
    },
  },
});

$(".js-data-example-ajax-skema").select2({
  ajax: {
    url: "Rencanakegiatan/skemaview",
    dataType: "json",
    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
    data: function (params) {
      var queryParameters = {
        term: params.term,
      };
      return queryParameters;
    },
    processResults: function (data) {
      return {
        results: $.map(data, function (item) {
          return {
            text: item.id_jabatan_kerja + " -> " + item.jabatan_kerja,
            id: item.id_jabatan_kerja,
          };
        }),
      };
    },
  },
});

$(".js-data-example-ajax-timpemutus").select2({
  ajax: {
    url: "Rencanakegiatan/timpemutus",
    dataType: "json",
    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
    data: function (params) {
      var queryParameters = {
        term: params.term,
      };
      return queryParameters;
    },
    processResults: function (data) {
      return {
        results: $.map(data, function (item) {
          return {
            text: item.nama_lengkap,
            id: item.tp_id,
          };
        }),
      };
    },
  },
});

$(".js-data-example-ajax-asesor").select2({
  ajax: {
    url: "Rencanakegiatan/lspasesor",
    dataType: "json",
    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
    data: function (params) {
      var queryParameters = {
        term: params.term,
      };
      return queryParameters;
    },
    processResults: function (data) {
      return {
        results: $.map(data, function (item) {
          return {
            text: item.sertifikat_asesor_bnsp + " -> " + item.nama_asesor,
            id: item.asesor_id,
          };
        }),
      };
    },
  },
});

function notifikasi(idkegiatan) {
  alert(idkegiatan);
}

function syncBnsp(idkegiatan) {
  $.ajax({
    url: "Rencanakegiatan/syncbnsp/" + idkegiatan,
    dataType: "json",
  }).done(function (data) {
    if (data?.res) {
      let o = JSON.parse(data.res);
      if (o.code != "ERR") {
        alert("Berhasil Sync ke BNSP");
      } else {
        alert(o.message);
      }
    }

    if (data.success === false) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: data?.message,
      });
    }
  });
}

function historyBnsp(var1) {
  // console.log(var1);
  kegiatan = var1;
  $("input[name='id_kegiatan']").val(var1);
  $("#Modelhistorydialog").modal("show");

  $("#history_view").DataTable({
    pageLength: 10,
    searching: false,
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"],
    ],
    paging: true,
    autoWidth: false,
    responsive: true,
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/historybnspdata/" + var1,
    },
    columns: [
      {
        data: "nomorku",
        orderable: true,
      },
      {
        data: "id_kegiatan",
        orderable: true,
      },
      {
        width: "10%",
        data: "body",
        orderable: true,
      },
      {
        width: "10%",
        data: "response",
        orderable: true,
      },
      {
        data: "note",
        orderable: true,
      },
      {
        data: "tanggal",
        orderable: true,
      },
    ],
  });
}
function historyLpjk(var1) {
  // console.log(var1);
  kegiatan = var1;
  $("input[name='id_izin']").val(var1);
  $("#ModelhistoryLpjkdialog").modal("show");

  $("#historyLpjk_view").DataTable({
    pageLength: 10,
    searching: false,
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"],
    ],
    paging: true,
    autoWidth: true,
    responsive: true,
    processing: true,
    serverSide: true,
    destroy: true,
    ajax: {
      url: "Rencanakegiatan/historylpjkdata/" + var1,
    },
    columns: [
      { data: "nomorku" },
      { data: "id_izin" },
      { data: "type" },
      { width: "10%", data: "payload" },
      { width: "10%", data: "response" },
      { data: "created" },
    ],
  });
}

function sendWa(idkegiatan) {
  $.ajax({
    url: "Rencanakegiatan/sendwa/" + idkegiatan,
    dataType: "json",
  }).done(function (data) {
    alert("Berhasil Kirim Link Absensi");
  });
}
function revisionBnsp(idkegiatan) {
  $.ajax({
    url: "Rencanakegiatan/revisionbnsp/" + idkegiatan,
    dataType: "json",
  }).done(function (data) {
    alert("Berhasil Mengirim Revisi ke BNSP");
  });
}
function hapusasesi(idketigatan, idizin) {
  console.log(idketigatan, idizin);
  Swal.fire({
    title: "Apakah kamu yakin?",
    text: "Anda tidak Dapat menambah asesi lagi setelah asesor di setting",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "YA, Hapus!",
  }).then((result) => {
    if (result.value) {
      $.ajax({
        url: "Rencanakegiatan/hapusasesi",
        method: "post",
        dataType: "json",
        data: {
          id_kegiatan: idketigatan,
          idizin: idizin,
        },
        success: function (vvvx) {
          console.log(vvvx);
          $("#kegiatan_asesi_view").DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
              url: "Rencanakegiatan/asesidata/" + idketigatan,
            },
            columns: [
              { data: "id_kegiatan" },
              { data: "id_izin" },
              { data: "telepon" },
              { data: "nama" },
            ],
          });
        },
      });
      console.log(result);
    }
  });
}
function hapusasesor(idketigatan, idasesor) {
  Swal.fire({
    title: "Apakah kamu yakin?",
    text: "Anda tidak Dapat menambah asesi lagi setelah asesor di setting",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "YA, Hapus!",
  }).then((result) => {
    if (result.value) {
      $.ajax({
        url: "Rencanakegiatan/hapusasesor",
        method: "post",
        dataType: "json",
        data: {
          id_kegiatan: idketigatan,
          idasesor: idasesor,
        },
        success: function (vvvx) {
          console.log(vvvx);
          $("#kegiatan_asesor_view").DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
              url: "Rencanakegiatan/asesordata/" + kegiatan,
            },
            columns: [
              { data: "nomorku" },
              { data: "no_hp" },
              { data: "nama_asesor" },
              { data: "sertifikat_asesor_bnsp" },
            ],
          });
        },
      });
      console.log(result);
    }
  });
}
