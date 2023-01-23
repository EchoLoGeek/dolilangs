/* 
 * Copyright (C) 2023 Anthony Damhet <anthony.damhet@outlook.fr>
 *
 * This program and files/directory inner it is free software: you can 
 * redistribute it and/or modify it under the terms of the 
 * GNU Affero General Public License (AGPL) as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AGPL for more details.
 *
 * You should have received a copy of the GNU AGPL
 * along with this program.  If not, see <https://www.gnu.org/licenses/agpl-3.0.html>.

 */

(function ($) {
    "use strict";

    // NOTIFICATIONS
    setTimeout(() => {
        $('.notifs').slideUp(1200);
    }, 1800);

    // DOLILANGS
    if (document.body.classList.contains('dolilangs')){

        $(document).on('click','#btn-addline',function(){

            var addurl = $(this).data('addurl');
            var nblines = parseInt($('input[name="nblines"]').val());
            var nb_newline = nblines + 1;
            var use_langs = $(this).data('langs');
            var table = $('#addtrans-table tbody');
            
            $.post(addurl,{viewnumber : nb_newline, useLangs : use_langs},function(view){
                $('input[name="nblines"]').attr('value',nb_newline);
                table.append(view);
            });
        });

    }



    
})(jQuery);