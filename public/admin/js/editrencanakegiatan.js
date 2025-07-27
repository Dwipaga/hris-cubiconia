$("#jenis_tempat").change(function(){
    $.ajax({
        url : site_url+"Rencanakegiatan/idtempat",
        method : "GET",
        dataType: "json",
        data : {"kode":$(this).val()},
        beforeSend:function(D){
            // console.log(D);

        },
        afterSend:function(selesai){
            console.log(selesai);
        },
        success : function(response){
            $("#id_tempat").html("");
            response.forEach(element => {
             console.log(element);
             if(element.skegiatan == 1){
                 $("#id_tempat").append("<option style=\"color:#bdc2c7\" disabled value=\""+element.kode+"\">"+element.title_name+"</option>")
             }else{0
                 $("#id_tempat").append("<option value=\""+element.kode+"\">"+element.title_name+"</option>")
             }
            });
        }
    });
});
