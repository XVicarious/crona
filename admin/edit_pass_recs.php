<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="../js/admin/passwordRules.js"></script>
<form>
    <label for="minLength">Minimum Password Length</label><input id="minLength" value="6">
    Require:<br>
    Lowercase<input type="checkbox" class="requires" value="lowercase">
    Uppercase<input type="checkbox" class="requires" value="uppercase">
    Digits<input type="checkbox" class="requires" value="digits" checked>
    Special Characters<input type="checkbox" class="requires" value="specials">
    <input type="button" id="saveRules">
</form>