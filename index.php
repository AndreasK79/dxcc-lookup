<?php
/**
 * @author  LA8AJA, Andreas Kristiansen
 *
 * 2021-jun-26
 * v 0.1 alpha
 * 	- Based on work by DJ1YFK http://fkurz.net/ham/dxcc.html - porting everything to PHP
 * 
 * 2022-nov-21
 * v 0.2 alpha
 *  - Cleaned up for release on GitHub
 * 
 * 2023-jan-22
 * V 0.1 beta
 *  - Fixes for /MM and /AM to be DXCC none, since they do not count towards dxcc
 * 
 * 2023-mar-22
 * V 0.2 beta
 *  - Rewrote to a class instead, so that the constructor can read the cty-file. Better for masslookup.
  * 
 */

 require_once 'dxcc.php';
 ?>
<!DOCTYPE html>
<html>

<head>
    <title>DXCC Lookup</title>
    <meta name="Keywords" content="ADIF, distance, plotter" />
    <meta charset="utf-8" />
<style>
    body {
        font-size: 0.9em;
        font-family: sans-serif;
    }
    .form, .result, h1 {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .styled-table {
        border-radius:5px;
        overflow: hidden;
        border-collapse: collapse;
        margin: 25px 0;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }
    .styled-table thead tr {
        background-color: #009879;
        color: #ffffff;
        text-align: left;
    }
    .styled-table th,
    .styled-table td {
        padding: 12px 15px;
    }
    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    }
    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: #009879;
    }
    input[type=text] {
        border-radius:5px;
        padding:10px;
        border:1px solid #dddddd;
        box-shadow:0 0 15px 4px rgba(0,0,0,0.06);
        width: 260px;
    }
    .button {
        /* remove default behavior */
        appearance:none;
        -webkit-appearance:none;

        /* usual styles */
        padding:10px;
        border:none;
        background-color:#009879;
        color:#fff;
        font-weight:600;
        border-radius:5px;
        width: 260px;
        box-shadow:0 0 15px 4px rgba(0,0,0,0.06);
    }
    * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
</style>
</head>

<body>
    <div id="main">
        <div id="content">
            <h1>DXCC Lookup Form</h1>
            <div class="form">
                    <form action="?cmd=check" enctype="multipart/form-data" method="POST">
                        <p>Callsign<br />
                        <input type="text" name="callsign"></p>
                        <p>Own callsign (for bearing and distance)<br />
                        <input type="text" name="mycallsign"></p>
                        <input class="button" type="submit" name="submit" value="Check DXCC" />
                    </form>
            </div>    
            <div class="result">
                <?php
                if (isset($_GET['cmd']) && ($_GET['cmd'] == "check")) {
                    $dc = new dxcc();
                    $dc->validatecallsign(trim($_POST['callsign']), trim($_POST['mycallsign']));
                } ?>
            </div>
        </div>

    </div>
</body>
</html>