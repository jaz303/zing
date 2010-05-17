<html>
  <head>
    <title>Zing! - Exception Caught</title>
    <style type='text/css'>
      body, table { font: 12px/1.4 Helvetica, Arial; }
      pre, code { font: 12px/1.4 monospace; }
      
      td, th { vertical-align: top; padding: 3px; }
      th { font-weight: bold; text-align: right; padding-right: 10px; }
    
      .extracted-source { margin: 0; padding: 0; }
        .extracted-source li { margin: 0; padding: 0; display: block; list-style: none; }
        .extracted-source li:nth-child(odd) { background: #f0f0f0; }
        .extracted-source li:nth-child(even) { background: #e0e0e0; }
        .extracted-source pre { margin: 0; padding: 2px; }
        .extracted-source .highlight { background: #ffdddd !important; }
        
      #dump-tabs { height: 31px; margin: 0; padding: 0; }  
        #dump-tabs li { float: left; display: block; list-style: none; float: left; margin: 0 10px 0 0; padding: 0; }
        #dump-tabs a { padding: 10px; font-size: 11px; line-height: 1; height: 11px; display: block; background: #c0c0c0; font-weight: bold; text-decoration: none; color: black; }
          #dump-tabs a.selected { background: #f0f0f0; }
      .dump { background: #f0f0f0; padding: 10px; display: none; }
        .dump pre { margin: 0; padding: 0; }
      
    </style>
    <script type='text/javascript'>
      function init() {
        showDump(0);
      }
      
      function showDump(ix) {
        var tabs = document.getElementById('dump-tabs').getElementsByTagName('a');
        for (var i = 0; i < tabs.length; i++) {
          tabs[i].className = (ix == i) ? 'selected' : '';
        }
        var dumps = document.getElementById('dumps').getElementsByTagName('div');
        for (var i = 0; i < dumps.length; i++) {
          dumps[i].style.display = (ix == i) ? 'block' : 'none';
        }
      }
    </script>
  </head>
  <body onload='init()'>
    
    <h2>Unhandled Exception</h2>
    
    <table cellspacing='0' cellpadding='0' border='0'>
      <tr>
        <th>Class:</th>
        <td><code><?= get_class($exception) ?></code></td>
      </tr>
      <tr>
        <th>Message:</th>
        <td><code><?= htmlentities($exception->getMessage()) ?></code></td>
      </tr>
      <tr>
        <th>Code:</th>
        <td><code><?= $exception->getCode() ?></code></td>
      </tr>
      <tr>
        <th>File:</th>
        <td><code><?= $exception->getFile() ?></code></td>
      </tr>
      <tr>
        <th>Line:</th>
        <td><code><?= $exception->getLine() ?></code></td>
      </tr>
      <tr>
        <th>Backtrace:</th>
        <td><pre><?= $exception->getTraceAsString() ?></pre></td>
      </tr>
    </table>
    
    <h2>Extracted Source</h2>
    
    <? if ($source = file($exception->getFile())) { ?>
      
      <?php
        $start = $exception->getLine() - 5;
        if ($start < 0) $start = 0;
        $end = $start + 11;
        if ($end > count($source)) $end = count($source);
      ?>
      
      <ul class='extracted-source'>
        <? foreach ($source as $line_number => $line) { ?>
          <? $line_number += 1 ?>
          <? if ($line_number >= $exception->getLine() - 5 && $line_number <= $exception->getLine() + 5) { ?>
            <li class='<?= $line_number == $exception->getLine() ? 'highlight' : '' ?>'><pre><b><?= sprintf("%04d", $line_number) ?>:</b> <?= htmlentities($line) ?></pre></li>
          <? } ?>
        <? } ?>
      </ul>
    
    <? } else { ?>
      
      <p>Extracted source is not available</p>
      
    <? } ?>
    
    <h2>Request</h2>
    
    <?php
      $dump = array(
        'URI'         => $request->url(),
        'Method'      => strtoupper($request->method()),
        'Date/time'   => date('c', $request->timestamp()),
        'Client IP'   => $request->client_ip(),
        'Client Port' => $request->client_port()
      );
    ?>
    
    <table cellspacing='0' cellpadding='0' border='0'>
      <? foreach ($dump as $k => $v) { ?>
        <tr>
          <th><?= $k ?>:</th>
          <td><code><?= htmlentities($v) ?></code></td>
        </tr>
      <? } ?>
    </table>
    
    <h2>Parameters</h2>
    
    <ul id='dump-tabs'>
      <li><a href='#' onclick='showDump(0); return false;'>$_GET</a></li>
      <li><a href='#' onclick='showDump(1); return false;'>$_POST</a></li>
      <li><a href='#' onclick='showDump(2); return false;'>$_REQUEST</a></li>
      <li><a href='#' onclick='showDump(3); return false;'>$_COOKIE</a></li>
      <li><a href='#' onclick='showDump(4); return false;'>$_SERVER</a></li>
      <li><a href='#' onclick='showDump(5); return false;'>$_ZING</a></li>
    </ul>
    <div style='clear:left'></div>
    
    <div id='dumps'>
      <? foreach (array($_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER, $GLOBALS['_ZING']) as $ix => $array) { ?>
        <div class='dump' id='dump-<?= $ix ?>'>
          <pre><? var_dump($array) ?></pre>
        </div>
      <? } ?>
    </div>
    
  </body>
</html>
