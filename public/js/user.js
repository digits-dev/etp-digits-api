const noChannelPriv = [1, 3, 6, 12];

$("#id_cms_privileges").change(function() {
    const selectedPriv = parseInt($(this).val(), 10);
    const isNoChannelPriv = noChannelPriv.includes(selectedPriv);
    $("#store_masters_id, #channels_id").prop("required", !isNoChannelPriv);

    $("#form-group-channels_id .control-label .text-danger").toggle(!isNoChannelPriv);
    $("#form-group-store_masters_id .control-label .text-danger").toggle(!isNoChannelPriv);
});

$("#id_cms_privileges").trigger("change");
