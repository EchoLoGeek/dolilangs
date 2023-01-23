<?php
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

 */ ?>

 <tr id="addline-<?php echo $_POST['viewnumber']; ?>">
    <td><input type="text" name="transkey_<?php echo $_POST['viewnumber']; ?>" value="" class="form-control"></td>

    <?php $available_languages = explode(',', $_POST['useLangs']); ?>
    <?php foreach($available_languages as $keylang): ?>
        <td><input type="text" name="transcontent_<?php echo $_POST['viewnumber'].'_'.$keylang; ?>" class="form-control"></td>
    <?php endforeach; ?>
    
</tr>