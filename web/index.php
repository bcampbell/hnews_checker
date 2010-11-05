<?php
// PHP Web interface for hnews_checker.
//
// Expects hnews_checker to be executable, and in the same directory.
//
// We're using PHP as a template langauage here, essentially.
// 
$url = $_GET['url'];

emit_header();
emit_form($url);
if( $url ) {
    $results = check( $url );
    if( is_null( $results ) ) {
        ?>Error processing <?= $url ?><?php
    } else {
        emit_results( $results );
    }
}
emit_footer();



// call hnews_checker, and pull the results back into php
function check( $url )
{
    $ret = 0;
    $lines=array();
    $checker_bin = dirname(__FILE__) . "/hnews_checker";

    $cmd = "$checker_bin -j -f $url";
        print "$cmd\n";
    exec( $cmd, &$lines, &$ret );
    $out = implode( "\n", $lines );

    if( $ret != 0 ) {
        /* failed */
        /* TODO: make sure user sees decent error messages! */
        return null;
    }


    return json_decode( $out, TRUE );
}


// helper - a few select markdown conventions
// TODO: paragraphs/line breaks
// TODO: maybe just bite bullet and use full markdown. See how
// complex the message 'extra' fields become...
function micromarkdown( $md ) {
    $html = preg_replace( '/\[.*?\]\s*\(.*?\)/','<a href="\2">\1</a>', $md );
    return $html;
}

function emit_header()
{

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">   
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link src="style.css">
    <link rel="stylesheet" href="i/style.css"/>
    <link rel="icon" href="i/favicon.png"/>
</head>
<body>
<h1>hNews micro<b>format</b> sanity-checker<small>(_not_ a validator!)</small></h1>
<?php
}

function emit_form( $url ) {

?>
<form action="" method="GET">
<p>Enter the url of the hNews article you'd like to check.
<a href="http://vernon.mediastandardstrust.org/~ben/hnews_checker/?url=http%3A%2F%2Fvernon.mediastandardstrust.org%2F~ben%2Fhnews_checker%2Fexamples%2Fbad.html">example</a>
</p>
<div><label for="url">URL</label> <input id="url" name="url" value="<?= $url ?>" size="100"/></div>

<p><input type="submit" value="Check URL"/></p>
</form>
<?php

}


function emit_footer()
{

?>
<h2>Credits</h2>
<p>Written by Ben Campbell (<a href="http://mediastandardstrust.org">Media Standards Trust</a>).
Source code is <a href="http://github.com/bcampbell/hnews_checker">here</a>.</p>
<p>Uses a <a href="http://github.com/bcampbell/microtron">modified</a> version of the <a href="http://github.com/amccollum/microtron">Microtron</a> microformat parser</p>
<p>Page style based on <a href="http://hcard.geekhood.net/">hCard Validator</a> by Kornel Lesiński
(<a href="http://code.google.com/p/hcardvalidator/">Source</a>)</p>
<p>Icons are from <a href="http://tango.freedesktop.org/">Tango Icon Library</a>.</p>

</body>
</html>
<?php

}


function emit_results( $results ) {


?>
<div id="result">
<h2>Results</h2>
<?php
    // TODO: display a better summary!

/*    if len( err_lines )==0 and len( warn_lines )==0:
        print """<p class="valid">Hooray - No errors found!</p>"""
    else:
        print '<p class="invalid">%d Errors, %d Warnings</p>' % (len(err_lines), len(warn_lines) )
*/
?>

<ol>
<?php foreach( $results['messages'] as $msg ) { emit_message( $msg ); } ?>
</ol>
</div>
<h3>Page Source</h3>
<div id="source">
<?php
    $n = 1;
    $lines = preg_split( '/\R/', $results['html']);

    foreach( $lines as $foo=>$line ) {
        /* TODO: highlight errors/warnings/info 
        cls = ''
        if n in warn_lines:
            cls = ' class="warn"'
        if n in err_lines:
            cls = ' class="error"'
         */
?>
    <span id="line<?= $n ?>"><?= $n ?>: <code><?= htmlentities( $line ); ?></code></span><br/>
<?php
        ++$n;
    }
?>
</div>
<?php
}


function emit_message( $msg ) {
    $icons = array(
        'error'=> '<img src="i/error.png" alt="error"/>',
        'warn'=> '<img src="i/warn.png" alt="warning"/>',
        'info'=> '<img src="i/info.png" alt="info"/>',
    );
?>
    <li class="<?= $msg['kind'] ?>">
    <h4><?= $icons[$msg['kind']] ?> <?= $msg['msg'] ?></h4>
<?php if( array_key_exists( 'line', $msg ) ) {?>
    <p><a href="#line<?= $msg['line'] ?>">line <?= $msg['line'] ?></a></p>
<?php } ?>
<?php if( array_key_exists('extra',$msg) ) { ?>
    <p><?= micromarkdown( $msg['extra'] ) ?></p>
<?php } ?>
</li>

<?php

}

