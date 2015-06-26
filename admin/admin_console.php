<script src="../js/lib/jquery.js"></script>
<script src="../js/lib/materialize.js"></script>
<?php
$echoMe = '<ul class="collapsible" data-collapsible="accordion">
    <li>
        <div class="collapsible-header"><i class="mdi-action-schedule"></i>Timestamp Thresholds</div>
        <div class="collapsible-body">
            Stamp In Thresholds:<br/>
            <label for="in-minus-threshold">Before: </label><input id="in-minus-threshold" name="in-before" value="0" size="1"><br/>
            <label for="in-plus-threshold">After: </label><input id="in-plus-threshold" name="in-after" value="0" size="1"><br/>
            <br/>
            Stamp Out Thresholds:<br/>
            <label for="out-minus-threshold">Before: </label><input id="out-minus-threshold" name="out-before" value="0" size="1"><br/>
            <label for="out-plus-threshold">After: </label><input id="out-plus-threshold" name="out-after" value="0" size="1"><br/>
        </div>
    </li>
    <li>
        <div class="collapsible-header"><i class="mdi-communication-vpn-key"></i>Password Requirements</div>
        <div class="collapsible-body">
            NYI
        </div>
    </li>
    <li>
        <div class="collapsible-header"><i class="mdi-image-timelapse"></i>Timezone Settings</div>
        <div class="collapsible-body">
            NYI
        </div>
    </li>
    <li>
        <div class="collapsible-header"><i class="mdi-action-receipt"></i>Administration Logs</div>
        <div class="collapsible-body">
            <a class="cyan lighten-1 waves-effect waves-light btn" id="modlog-button">Open Modification Log</a><br/>
            <a class="cyan lighten-1 waves-effect waves-light btn" id="timestamp-button">Open Timestamp Log</a>
        </div>
    </li>
</ul>';
echo $echoMe;

// Does this need to be a PHP file? likely not
