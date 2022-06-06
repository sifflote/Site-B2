$(document).ready( function () {
    $('#table_id').DataTable({
        order: [[3, 'desc']],
        lengthMenu: [
            [100, 500, 1000 ,2000, -1],
            [100, 500, 1000 ,2000, 'Totalité'],
        ],
        pageLength: 1000,
        language: {
            paginate: {
                previous: "Précédente",
                next: "Suivante"
            },
            infoFiltered: "(filtrés sur _MAX_ rejets)",
            info : "_PAGE_ sur _PAGES_ page(s)",
            lengthMenu : "Afficher _MENU_ rejets",
            search : "Rechercher:"
        }
    });
});
$(document).ready( function () {
    //var originalModal =$('#empModal').clone();
    $('.titreModal').click(function(){
        var path = $(this).data('path');
        var titreRef = $(this).data('id');
        $.ajaxSetup ({
            // Disable caching of AJAX responses
            cache: false
        });
        $.ajax({
            type: 'POST',
            async: true,
            url: path,
            data: { reference: titreRef },
            cache: false,
            dataType: "json",
            success: function(r){
                //$('.modal-body').html(response);
                console.log(r.data);
                $('#modal-titre').removeClass();
                $('#modal-titre').addClass("modal-title bg-"+r.data.bgcolor+" text-"+r.data.color);
                $('#json_titre').text(r.data.type + ' - Titre n° '+r.data.reference);

                var rprs = r.data.rprs;
                $('#json_rprs_text').remove();
                if(r.data.rprs === 1){
                    $('#json_rprs').append('<span id="json_rprs_text" class="text-info"><i class="fa-solid fa-circle-check"></i> RPRS <i class="fa-solid fa-circle-check"></i></span>')
                }else(
                    $('#json_rprs').append('<span id="json_rprs_text" class="text-muted"><i class="fa-solid fa-circle-question"></i> RPRS <i class="fa-solid fa-circle-question"></i></span>')
                )

                $('#json_lot').text('Lot N° ' + r.data.lot + ' - Crée le ' + r.data.cree_at + ' - Rejeté le ' +r.data.rejet_at);


                $('#traitement_titre_text').removeClass();
                $('#traitement_titre_text').addClass("bg-"+r.data.bgcolor+" text-"+r.data.color);
                $('#traitement_titre_text').text(r.data.observation);


                $('#precision_text').text(r.data.precisions);
                const traiteAt = new Date(r.data.traite_at)
                $('#author_text').text(r.data.username+ ' - '+traiteAt.toLocaleDateString("fr"));

                $('#iep_text').text(r.data.iep);

                $('#ipp_text').text(r.data.ipp);

                const enterAt = new Date(r.data.enter_at)
                $('#entree_text').text(enterAt.toLocaleDateString("fr"));

                const exitAt = new Date(r.data.exit_at)
                $('#exit_text').text(exitAt.toLocaleDateString("fr"));
                var difference = exitAt.getTime() - enterAt.getTime();
                var days = Math.ceil(difference / (1000 * 3600 * 24)+1);
                if(days === 1 )
                {
                    $('#jours_text').text(days+' journée');
                }else{
                    $('#jours_text').text(days+' jours');
                }

                $('#uh_text').text(r.data.designation + ' - ' +r.data.numero);
                $('#antenne_text').text(r.data.antenne);

                $('#client_text').text(r.data.payeur + ' - ' +r.data.name);

                $('#rejet_text').text(r.data.code_rejet + ' - ' +r.data.desc_rejet);

                $('#contrat_text').text(r.data.contrat + ' - ' +r.data.pec);

                $('#montant_text').text(new Intl.NumberFormat('fr-FR', {style: 'currency', currency: 'EUR'}).format(r.data.montant));
                $('#encaissement_text').text(new Intl.NumberFormat('fr-FR', {style: 'currency', currency: 'EUR'}).format(r.data.encaissement));
                $('#restant_text').text(new Intl.NumberFormat('fr-FR', {style: 'currency', currency: 'EUR'}).format(r.data.restantdu));

                $('#insee_text').text(r.data.insee + ' - ' +r.data.rang);

                const naissance = new Date(r.data.naissance_at)
                $('#naissance_text').text(naissance.toLocaleDateString("fr") + ' - ' +r.data.naissance_hf);

                $("#flexSwitchCheckDefault").removeAttr('checked');
                if(r.data.rprs === 1){
                    $('#flexSwitchCheckDefault').attr( 'checked', 'checked' )
                }

                $("#form_ref").val(r.data.reference);
                $('#traitement_form_precisions').val(r.data.precisions);

                $('#traitement_form_observation option').removeAttr('selected');
                $('#traitement_form_observation option[value="'+ r.data.observation_id + '"]').prop('selected', true);



                $('#empModal').modal('show');
                console.log(r.data.rprs);
            }
        });
    });
});