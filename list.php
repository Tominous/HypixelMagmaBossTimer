<style>
    table, th, td {
        border: 1px solid black;
    }
    .unreliable{
        background-color:gray;
    }
</style>

<?php

include_once "common.php";

include "db_stuff.php";


$stmt = $conn->prepare("SELECT id,server,last_spawn,lava_level,lava_level_time,lowest_stream FROM hypixel_skyblock_magma_timer ORDER BY lava_level_time ASC, lava_level ASC, last_spawn DESC, server ASC");
$stmt->bind_param("s", $server);
$stmt->execute();
$stmt->bind_result($id, $server, $last_spawn, $lava_level, $lava_level_time,$lowest_stream);


echo "<table style='width: 100%'>";
echo "
<tr>
<th>Server</th>
<th>Lava Level (above ground)</th>
<th>Lowest Lava Stream</th>
<th>Lava Level Time</th>
<th>Last Spawn</th>
<th>Estimated Next Spawn</th>
</tr>";

while ($row = $stmt->fetch()) {

    if (!isset($last_spawn)) {
        $last_spawn = "Unknown";
    }


    $lava_level_timestamp = strtotime($lava_level_time);
    $last_spawn_timestamp = strtotime($last_spawn);

    $next_spawn = estimateNextSpawn($lava_level, $lava_level_time, $last_spawn,$lowest_stream);


    $lava_level -= $FLOOR_LEVEL;

    $stream_percent = 100;
    if (isset($lowest_stream)&&!empty($lowest_stream)) {
        $stream_percent= round($lava_level/($LAVA_STARTS[$lowest_stream]-$FLOOR_LEVEL)*100);
    }else{
        $lowest_stream = "N/A";
    }

    echo "
<tr>
<td class='server' data-server='$server'><a href='check.php?server=$server'>$server</a></td>
<td class='lava_level' data-level='$lava_level'>$lava_level</td>
<td class='lowest_stream'>$lowest_stream ($stream_percent%)</td>
<td class='lava_level_time' data-time='$lava_level_timestamp'>$lava_level_time</td>
<td class='last_spawn' data-time='$last_spawn_timestamp'>$last_spawn</td>
<td class='next_spawn' ".(is_numeric($next_spawn)?("data-time='$next_spawn'"):("")).">$next_spawn</td>
</tr>
";
}


echo "</table>";

?>

<br/>
<a href="add.php">Add Stuff!</a>
<br/>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {


        function refreshTimes() {
            $("td[data-time]").each(function () {
                let $this = $(this);
                let timeString = $this.text();
                let timestamp = $this.data("time");
                if (timestamp == null || timestamp === 0 || timestamp.length === 0) {
                    return;
                }
                let parsed = moment.unix(timestamp);

                if ($this.hasClass("lava_level_time")) {
                    if(Math.abs(parsed.diff(moment()))>7.2e+6/*2h*/)
                    $this.parent().addClass("unreliable");
                }

                let formatted = parsed.format('lll');

                let fromNow = parsed.fromNow();
                let toNow = parsed.toNow();

                $this.text(fromNow + "  (" + formatted + ")");
            })
        }

        refreshTimes();
        setInterval(refreshTimes, 30000);

    })
</script>
