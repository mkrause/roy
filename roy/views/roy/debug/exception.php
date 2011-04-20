<h1>Oh noes!</h1>
<? foreach ($exceptions as $e): ?>
    <h2>
        Exception of type <code><?= html::encode($e['class']) ?></code> was thrown in file<br>
        <code><?= html::encode($e['file']) ?></code> on line <code><?= html::encode($e['line']) ?></code>.
    </h2>
    <div class="exception-details">
        <p>
            <?= html::encode($e['message']) ?>
        </p>
        <h3>Trace</h3>
        <p>
            <pre><?= html::encode($e['trace']) ?></pre>
        </p>
    </div>
<? endforeach ?>
<p>
    To change this view, override the layout view <code>/roy/debug/layout.php</code> and the
    content view <code>/roy/debug/exception.php</code>.
</p>
