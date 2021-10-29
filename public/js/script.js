$(function (){
    // Script pour voir le nom du fichier dans une entrée de fichier bootstrap
    $('.custom-file-input').on('change', e => {
        let inputFile = e.currentTarget
        $(inputFile).parent().find('.custom-file-label').html(inputFile.files[0].name)
    })

})