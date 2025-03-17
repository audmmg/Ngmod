$( document ).ready(function() {
    //dropzone
    if($("#drop-place").length)
    var myDropzone = new Dropzone("#drop-place",
        {
            url: "?rc=ajax/fileUpload&rd=nz&",
            params: { updatenr: $('#dokumento_id_dropzone').val(),type:0 },
            success:function(file, responseText){}
        }
    );

    if($("#drop-place-type-1").length)
    var myDropzone = new Dropzone("#drop-place-type-1",
        {
            url: "?rc=ajax/fileUpload&rd=nz&",
            params: { updatenr: $('#dokumento_id_dropzone').val(),type:1 },
            success:function(file, responseText){}
        }
    );

    if($("#drop-place-type-2").length)
    var myDropzone = new Dropzone("#drop-place-type-2",
        {
            url: "?rc=ajax/fileUpload&rd=nz&",
            params: { updatenr: $('#dokumento_id_dropzone').val(),type:2 },
            success:function(file, responseText){}
        }
    );

    if($("#drop-place-type-3").length)
    var myDropzone = new Dropzone("#drop-place-type-3",
        {
            url: "?rc=ajax/fileUpload&rd=nz&",
            params: { updatenr: $('#dokumento_id_dropzone').val(),type:3 },
            success:function(file, responseText){}
        }
    );
    //vizitu dropas
    if($("#drop-place-type-4").length)
    var myDropzone = new Dropzone("#drop-place-type-4",
        {
            url: "?rc=ajax/fileUpload&rd=nz&",
            params: { updatenr: $('#dokumento_id_dropzone').val(),type:4 },
            success:function(file, responseText){}
        }
    );
    ///

    $(".chosen-select").chosen();

    $( "#datepicker,#datepicker2,#datepicker_n,#datepicker_i, #datepicker_s, #datepicker_b" ).datepicker();

    $(document).on("click", ".manual-ajax", function(event) {
      event.preventDefault();
      $.get(this.href, function(html) {
        $(html).appendTo('body').modal();
         $( "#datepicker-modal" ).datepicker();
        //dropzone
        var myDropzone = new Dropzone("#drop-place-task",
            {
                url: "?rc=ajax/fileUpload&rd=nz&",
                params: { updatenr: $('#dokumento_id_dropzone').val(),updateuser: $('#user_id_dropzone').val(),type: $('#task-type').val() },
                success:function(file, responseText){}
            }
        );
        ///
      });
    });


    $("a.nz-delete").click(function(e) {
            e.stopPropagation();
    });

    $("#check_state").on("change",function(){
        statej=$('#check_state').val();
        if(statej==30)$('#check_state_butt').toggle();
    });



    $( "#client_search" ).autocomplete({
      source: "index.php?rc=clientSearch&rd=nz",
      minLength: 2,
      select: function( event, ui ) {
        //log( "Selected: " + ui.item.value + " aka " + ui.item.id );
        //alert(ui.item.value);
      }
    } );

    $( "#client-name" ).autocomplete({
      source: "index.php?rc=clientSearch&rd=nz",
      minLength: 2,
      select: function( event, ui ) {
        //log( "Selected: " + ui.item.value + " aka " + ui.item.id );
        //alert(ui.item.id);
        $('#kliento_id, #client-id').val(ui.item.id);
        search_client();
      }
    } );

    $( "#zaliavos_tiekejas" ).autocomplete({
      source: "index.php?rc=supplierSearch&rd=nz",
      minLength: 2,
      select: function( event, ui ) {
        //log( "Selected: " + ui.item.value + " aka " + ui.item.id );
        //alert(ui.item.value);
        $('#kontaktinis_asmuo').val(ui.item.id);
      }
    } );

///on enter press submit form
$("#list-place").on( "keypress", function(event) {
    if (event.which == 13 && !event.shiftKey) {
        event.preventDefault();
        $("#filter-form").submit();
    }
});


$(document).on("click", ".filter-slogan", function(event){
    event.preventDefault();
   $('#filter-cont').toggle();
});

$(document).on("change", "#change_other_contact", function(event){
    event.preventDefault();
    reiksme=$('#change_other_contact').val();
    $('#client-contact, #kontaktinis-asmuo, #kontaktinis-email').val(reiksme);
});

$("form").on("submit", function(e){
    if($('#has_not_saved_persons').length){
       var answer = confirm("Ar tikrai norite išsaugoti formą nepridėję susijusio asmens?");
        if(answer){
            return true;
        }else{
             e.preventDefault();
        }
    }
 })

$(document).on('change', '#p1, #p2, #p3, #pp1, #pp2, #pp3', function() {
  if(!$('#has_not_saved_persons').length){
    $('body').append('<div id="has_not_saved_persons"></div>');
  }
});

$(document).on('click', '#createRootCause', function() {
    auditQuery = '&type=0';
    if ( $( "#audit-type" ).length ) {
        auditQuery='&type=1';
    }
    $.get("?rc=rootCaseForm&rd=nz&uid="+$('#uid').val()+auditQuery).done(function (data) {
        $('#rootcause-place').html($(data).find(".rootcausecontent")[0]);
        $(".chosen-select").chosen();
    })
});

$(document).on('change', '#rootCauseAddPerson', function() {
    personName = $('#rootCauseAddPerson').val();
    personHtml='<li><input type="checkbox" name="p[susijes][]" value="'+personName+'" checked="checked"/><span style="margin-top:7px">'+personName+'</span></li>';
    $('#rootCausePersons').append(personHtml);
});



/* *********** ARNO ************ */

$(".saveClick").on( "click", function(event) {
    var ErMessage='N';

    //var emailaddress = document.getElementById("kontaktinis-email").value;
    
    var emailaddress = $('#kontaktinis-asmuo').val();
    //alert ('mail: ' + emailaddress);

    if( (emailaddress=='' || !validateEmailMF(emailaddress)) && ($('#prBusena').val()=='0') ) { 
        ErMessage='\nNe įvestas arba neteisingai įvestas kliento el. paštas';
        $('#kontaktinis-asmuo').css({'background-color' : '#ffcccc'});
        $('#kontaktinis-asmuo-error').html("Įveskite teisingą el. pašto adresą.");
    }else{

    }

    if(ErMessage=='N'){
        $('#new-pretnzija').submit();
        //alert ('OK '+ErMessage);
    }else{
        alert (ErMessage);
    }

});


$(".saveEditClick").on( "click", function(event) {
    var ErMessage='N';

    //var emailaddress = document.getElementById("kontaktinis-email").value;
    
    var emailaddress = $('#kontaktinis-asmuo').val();
    //alert ('mail: ' + emailaddress);

    if( (emailaddress=='' || !validateEmailMF(emailaddress)) && ($('#check_state').val()=='0') ) { 
        ErMessage='\nNe įvestas arba neteisingai įvestas kliento el. paštas';
        $('#kontaktinis-asmuo').css({'background-color' : '#ffcccc'});
        $('#kontaktinis-asmuo-error').html("Įveskite teisingą el. pašto adresą.");
    }else{

    }

    if(ErMessage=='N'){
        $('#edit-pretenzija').submit();
        //alert ('OK '+ErMessage);
    }else{
        alert (ErMessage);
    }

});


$("#kontaktinis-asmuo").on("focus", function(event) {
    $('#kontaktinis-asmuo').css({'background-color' : '#ffffff'});
    $('#kontaktinis-asmuo-error').html("");
});




});// end jq









/* ******************* FUNKCIJOS ********************** */

function toggleTrinama(id){
    $('#'+id).toggle();
}

    function activateDash(id){
        $('.act-dash').removeClass('act-dash');
        $(id).addClass('act-dash');
        $('.dashboard-links').css('display','none');
    }

function add_person(){
    vardas=$('#asmuo-name').val();
    data_iki=$('#datepicker').val();
    uzduotis=$('#asmuo-uzduotis').val();
    busena=$('#asmuo-busena').val();
    veiksmas=$('#asmuo-veiksmas').val();
    taskurl=$('#taskurl').val();
    uid=$('#uid').val();
    type=$('#task-type').val();
    padalinys=$('#asmuo-padalinys').val();
    $.post("index.php?rc=asmenys&rd=nz", {vardas:vardas,data_iki:data_iki,uzduotis:uzduotis,busena:busena,uid:uid,type:type,pad:padalinys,veiksm:veiksmas,linkas:taskurl}, function(data){
        $("#asmenys-place").load("index.php?rc=asmenysList&rd=nz&uid="+uid+"&type="+type, function() {
              $( "#datepicker,#datepicker2" ).datepicker();
            }
        );
    });

}

function edit_person(){



    data_ikij=$('#datepicker-modal').val();
    uzduotisj=$('#asmuo-uzduotis-modal').val();
    uzduotisjlang=$('#asmuo-uzduotis-modal-lang').val();
    busenaj=$('#asmuo-busena-modal').val();
    veiksmaij=$('#asmuo-veiksmas-modal').val();
    uidj=$('#task-uid').val();
    uid2j=$('#uid').val();


    fo1j=$('#dokumento_id_dropzone').val();
    fo2j=$('#user_id_dropzone').val();

    typj=$('#task-type').val();

    flj="";
    $('#ajax-delete-file input:checked').each(function() {
       //console.log(this.value);
       flj=flj+this.value+'8mx8';
    });
    $.post("index.php?rc=asmenysEdit&rd=nz", {data_iki:data_ikij,uzduotis:uzduotisj,uzduotislang:uzduotisjlang,busena:busenaj,uid:uidj,save:'1',removef:flj,fo1:fo1j,fo2:fo2j,type:typj,veiks:veiksmaij}, function(data){
        //$("#asmenys-place").load("index.php?rc=asmenysList&rd=nz&uid="+uid2);
    });
}

function change_audit_type(){
    id=$('#audit-type').val();
    $('.auti-type').removeAttr('style');
    $('#audit'+id).css('display','block');
    if(id==2)
    $('#audit-contacts').css('display','block');
}

function search_client(){
    client_id=$('#kliento_id').val();
    if($('#client-id'))client_id=$('#client-id').val();
    if(client_id.length > 5)
    $.post("index.php?rc=GetClient&rd=nz", {clientid:client_id}, function(data){
        var obj = jQuery.parseJSON(data);
        $('#client-name').val(obj.name);
        $('#client-contact, #kontaktinis-asmuo').val(obj.contact);
        $('#sel_other_contact').html(obj.select);
    });
}

function search_client_kp(){/* tik kliento pretenzijoje*/
    client_id=$('#kliento_id').val();
    if($('#client-id'))client_id=$('#client-id').val();
    if(client_id.length > 5)
    $.post("index.php?rc=GetClient&rd=nz", {clientid:client_id}, function(data){
        var obj = jQuery.parseJSON(data);
        $('#client-name').val(obj.name);
        //$('#client-contact, #kontaktinis-asmuo').val(obj.contact);
        //$('#sel_other_contact').html(obj.select);
    });
}

function get_zaliava(){
    zaliava_id=$('#zaliava_id').val();
    if(zaliava_id.length > 5){
        $.post("index.php?rc=GetZaliava&rd=nz", {zaliavaid:zaliava_id}, function(data){
            var obj = jQuery.parseJSON(data);
            $('#zaliavos-pavadinimas').val(obj.ZalPavad);
            $('#tiekejo-artikelis').val(obj.ZalKodas);
            //
            $.post("index.php?rc=GetClientContacts&rd=nz", {tiekejasid:obj.ZalTiekejasID}, function(data2){
                var obj2 = jQuery.parseJSON(data2);
                $('#zaliavos_tiekejas').val(obj2.CI_Pavadinimas);
                $('#kontaktinis_asmuo').val(obj2.jrasm);
            });
            //
        });
    }
}

function get_zaliava_by_material(){
    zaliava_id=$('#zaliava_id').val();
    if(zaliava_id.length > 5){
        $.post("index.php?rc=GetZaliavaMaterial&rd=nz", {zaliavaid:zaliava_id}, function(data){
            var obj = jQuery.parseJSON(data);
            $('#zaliavos-pavadinimas').val(obj.ZalPavad);
            $('#tiekejo-artikelis').val(obj.ZalKodas);
            //
            $('#zaliavos_tiekejas').val(obj.TKPavad);
            $('#kontaktinis_asmuo').val(obj.TKEmail);
            //
        });
    }
}

function getJobDuomByJobID(){

    JobID=$('#job_id').val();
    if(JobID.length > 5)
    $.post("index.php?rc=getJobDuomByJobID&rd=nz", {job_id:JobID}, function(data){

//var obj = JSON.parse(data);

//var obj_s = JSON.stringify(data);

var obj = jQuery.parseJSON(data);

//var obj = JSON.parse(JSON.stringify(data));

//console.log(obj);

//console.log(obj.ClientID);

//console.log("testas");

        $('#client-name').val(obj.ClientName);
        $('#client-id').val(obj.ClientID);
        $('#aptarnaujantis-vadybininkas').val(obj.Aptarnaujantis);
        //$('#kontaktinis-asmuo').val(obj.ClientTel+" "+obj.ClientEmail);
        $('#geri-metrai').val(obj.ZalSunaudGeriMetrai);
        //$('#kontaktinis-email').val(obj.ClientEmail);
        $('#kontaktinis-lang').val(obj.ClientKalba);
        $("#order-info-ajax").load("index.php?rc=uzsakymoInformacija&rd=nz&job_id="+JobID, function() {
          $(".chosen-select").chosen();
        });
        $('#hidden-user').css('display','block');
        $(window).scrollTop($('#order-info-ajax').offset().top);
    });
}

function load_more_products(uid){
    $("#order-info-ajax").load("index.php?rc=uzsakymoInformacijaAdd&rd=nz&job_id="+uid, function() {
        $(".chosen-select").chosen();
    });
}



function add_person_to_item(id,nr){

    p1=$('#product'+nr+' #p1').val();
    if(p1=='' || p1=='-'){
        alert('Pasirinkite padalinį');return;
    }
    p2=$('#product'+nr+' #p2').val();
    p3=$('#product'+nr+' #p3').val();
    if ($("#p4").is(":checked")) {
        p4=$('#p4').val();
    } else {
        p4 = '';
    }
    content="<div>"+p2+" "+p1+" "+p3+" "+p4+"<input type='hidden' name='p[gaminys]["+nr+"][darbuotojas][]' value='"+p2+" "+p1+" "+p3+" "+p4+"'/><i class='fa fa-times' aria-hidden='true' onclick=' remove_susijes(this);'></i></div>";
    $('#ajax'+id).append(content);
    $('#product'+nr+' #p1').prop('selectedIndex', 0);
    $('#product'+nr+' #p2').prop('selectedIndex', 0);
    $('#product'+nr+' #p3').prop('selectedIndex', 0);
    $('#product'+nr+' #p4').prop('checked', false);
    $('#has_not_saved_persons').remove();

}

function add_person_to_project(id,nr){
    p1=$('#pp1').val();
    if(p1=='' || p1=='-'){
        alert('Pasirinkite padalinį');return;
    }
    p2=$('#pp2').val();
    p3=$('#pp3').val();
    if ($("#pp4").is(":checked")) {
        p4=$('#pp4').val();
    } else {
        p4 = '';
    }
    content="<div>"+p2+" "+p1+" "+p3+ " "+p4+"<input type='hidden' name='p[darbuotojas][]' value='"+p2+" "+p1+" "+p3+" "+p4+"'/><i class='fa fa-times' aria-hidden='true' onclick=' remove_susijes(this);'></i></div>";
    $('#ajax'+id).append(content);
    $('#product'+nr+' #pp1').prop('selectedIndex', 0);
    $('#product'+nr+' #pp2').prop('selectedIndex', 0);
    $('#product'+nr+' #pp3').prop('selectedIndex', 0);
    $('#product'+nr+' #pp4').prop('checked', false);
     $('#has_not_saved_persons').remove();
}

function add_person_to_vizit(id){
    p1=$('#p1').val();
    if(p1=='' || p1=='-'){
        alert('Pasirinkite padalinį');return;
    }
    p2=$('#p2').val();
    p3=$('#p3').val();
    if ($("#p4").is(":checked")) {
        p4=$('#p4').val();
    } else {
        p4 = '';
    }
    content="<div>"+p2+" "+p1+" "+p3+" "+p4+ "<input type='hidden' name='p[darbuotojas][]' value='"+p2+" "+p1+" "+p3+" "+p4+"'/><i class='fa fa-times' aria-hidden='true' onclick=' remove_susijes(this);'></i></div>";
    $('#ajax'+id).append(content);
    $('#product'+nr+' #p1').prop('selectedIndex', 0);
    $('#product'+nr+' #p2').prop('selectedIndex', 0);
    $('#product'+nr+' #p3').prop('selectedIndex', 0);
    $('#product'+nr+' #p4').prop('checked', false);
     $('#has_not_saved_persons').remove();
}

function remove_susijes(a){
    var answer = confirm("Ar tikrai norite atlikti trynimą?");
    if(answer){
        $(a).parent().remove();
    }
}

//skaiciuojam nuostolius keiciant reiksmes tiesiogiai
function skaiciuojam_nuostolius(tevas){
    //matas=$('#'+tevas+' .size_2').val();
    matas=$('#'+tevas+' #kvnt').val();
    //if(matas==0)daliklis=1000;else daliklis=1;
    if(matas=='/1000')daliklis=1000;else daliklis=1;
    kiekis=parseFloat($('#'+tevas+' #calc2').val());
    if($('#'+tevas+' #calc2').val()==''){
        kiekis=0;
        $('#'+tevas+' #calc2').val(0);
    }
    kaina=parseFloat($('#'+tevas+' #itm-kaina').val());
    nuostolis=kiekis*kaina;
    nuostolis=nuostolis/daliklis;


    $('#'+tevas+' #calc3').val(nuostolis.toFixed(2));
    pap=$('#'+tevas+' #calc4').val();
    if(pap=="")pap=0;
    pap=pap;
    papildomas=parseFloat(pap);
    bendras=nuostolis+papildomas;

    $('#'+tevas+' #calc5').val(bendras.toFixed(2));

    //suskaiciuojam bendra uzsakymo nuostoli
    b=0;
    $('.bendras-nuostolis').each(function(){
        n=parseFloat($(this).val());
        if($(this).val()=='')n=0;
        b=b+n;
    })
    $('#calc1').val(b.toFixed(2));
}

function add_rulonas(){
    id=0;
    reiksme=0;
    $( ".adding_element" ).each(function() {
      reiksme=$( this ).attr('data-numeris');
        reiksme=parseInt(reiksme);
        reiksme=reiksme+1;
    });
    newhtml='<div class="adding_element" data-numeris="'+reiksme+'" id="rulonas'+reiksme+'">\
                <div class="nz-col-3">\
                    <div class="label-left">\
                        <label>SKU ID:</label>\
                        <input type="text" name="rulonas['+reiksme+'][id]" />\
                    </div>\
                </div> \
                <div class="nz-col-3">\
                    <div class="label-left">\
                        <label>Sandelio lokacija:</label>\
                        <select name="rulonas['+reiksme+'][sandelis]">\
                            <option value="0">Žaliavos_KPG</option>\
                            <option value="1">Žaliavos_KEG</option>\
                            <option value="2">Žaliavos_Sandėlys</option>\
                            <option value="3">Kliento pretenzija</option>\
                        </select>\
                    </div>\
                </div>\
                <div class="nz-col-3">\
                    <div class="label-left">\
                        <a class="nz-icon-butt" onclick="remove_rulonas('+reiksme+')"><i class="fa fa-times" aria-hidden="true"></i> Šalinti</a>\
                    </div>\
                </div><div class="clear"></div>\
            </div>';
    $('#adding-place').append(newhtml); newhtml="";
}

function remove_rulonas(id){
    $('#rulonas'+id).remove();
}

function auto_reikalavimai(){
    nr=$('#auto-reikalavimai-val').val();
    if(nr==0){
        $('#auto-reikalavimai').val('Compensation');
    }
    else{
        $('#auto-reikalavimai').val('Just for information');
    }
}

function selectJobs(){
    $( "input.select_remove:not(:checked)" ).each(function( index ) {
      nr=$( this ).val();
      $('#product'+nr).remove();
      $('#remove'+nr).remove();
    });
}

function selectAll(cl){
    $(cl).attr('onclick',"removeAll(this)");
    $(cl).text('Atžymėti visus');
    $('input.select_remove').each(function() {
        this.checked = true;
    });
}

function removeAll(cl){
    $(cl).attr('onclick',"selectAll(this)");
    $(cl).text('Pasirinkti visus');
    $('input.select_remove').each(function() {
        this.checked = false;
    });
}

function delete_neatitiktis(nr){
    $( "#dialog-confirm" ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Taip": function() {
          $('#table'+nr).slideToggle();
            $.post("index.php?rc=listDelete&rd=nz", {uid:nr}, function(data){

            });
          $( this ).dialog( "close" );
        },
        "Ne": function() {
          $( this ).dialog( "close" );
        }
      }
    });
}

function delete_vizitas(nr){
    $( "#dialog-confirm" ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Taip": function() {
          $('#table'+nr).slideToggle();
            $.post("index.php?rc=listDVizit&rd=nz", {uid:nr}, function(data){

            });
          $( this ).dialog( "close" );
        },
        "Ne": function() {
          $( this ).dialog( "close" );
        }
      }
    });
}

function removeperson(nr,it){
    var answer = confirm("Ar tikrai norite atlikti trynimą?");
    if(answer){
        $(it).parent().remove();
        $.post("index.php?rc=asmuoDe_lete&rd=nz", {uid:nr}, function(data){});
    }
}

function removepersonAudit(nr,it){
    var answer = confirm("Ar tikrai norite atlikti trynimą?");
    if(answer){
        $(it).parent().remove();
        $.post("index.php?rc=asmuoDe_leteAudit&rd=nz", {uid:nr}, function(data){});
    }
}

function removepersonVisit(nr,it){
    var answer = confirm("Ar tikrai norite atlikti trynimą?");
    if(answer){
        $(it).parent().remove();
        $.post("index.php?rc=asmuoDe_leteVisit&rd=nz", {uid:nr}, function(data){});
    }
}

function remove_atsakingas_person(nr){
    var answer = confirm("Ar tikrai norite atlikti trynimą?");
    if(answer){
        $('#asm'+nr).remove();
        $.post("index.php?rc=asmuo_atsakingasDe_lete&rd=nz", {uid:nr}, function(data){});
    }
}

function print_pg(){
    $('.nz-form-title, .filter, .paging, .list_docs, #topmenu, #prisijunges, #print-back').toggleClass('print');
    $('body').toggleClass('print_small');
    window.print();
}

function print_back(){
    $('.nz-form-title, .filter, .paging, .list_docs, #topmenu, #prisijunges, #print-back').toggleClass('print');
    $('body').toggleClass('print_small');
}


/* ************************** ARNO funkcijos ************************** */

function validateEmailMF($email) {
  var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
  return emailReg.test( $email );
}


/*
function saveNewPretenzija(){
    var ErMessage='N';

    var emailaddress = document.getElementById("kontaktinis-email").value;
    alert ('mail: ' + emailaddress);

    if( emailaddress=='' || !validateEmailMF(emailaddress)) { 
        ErMessage='Neįvestas arba neteisingai įvestas kontaktinio asmens el. paštas';
    }else{

    }

    if(ErMessage=='N'){
        //$('#new-pretnzija').submit();
        alert ('OK '+ErMessage);
    }else{
        alert (ErMessage);
        alert ('NOTOK ' + ErMessage);
    }

    
}
*/