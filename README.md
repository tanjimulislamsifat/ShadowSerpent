# ShadowSerpent Proshell

 ![Image Alt](https://github.com/tanjimulislamsifat/ShadowSerpent/blob/a272c82fa594243480d1b641ea8f7968e5855d15/shadowserpent.jpg)

📖 About

ShadowSerpent v2: NextGen is the pinnacle of PHP reverse shells, evolved for insane-level robustness in ethical pentesting. It builds on v1's strengths (SSL/TCP fallback, multi-shell, retries, non-blocking I/O) while eradicating weaknesses (firewall handling, auto-reconnect, cross-platform, unobfuscated elements, no encryption, edges).

| Feature | Description | Evasion/Benefit |
|---------|-------------|-----------------|
| **Multi-Transport** | SSL/TCP primary, HTTP polling fallback | Bypasses outbound filters; common ports blend traffic |
| **Auto-Reconnect** | Exponential backoff + jitter on drops | Persistent in flaky nets; evades timing-based IDS |
| **Cross-Platform** | Windows/Linux shells (cmd/powershell vs bash/sh) | Works in mixed envs (Docker, VMs) |
| **Insane Obfuscation** | Chr/ord, rot13+base64 layers, dynamic builds | Bypasses ANY WAF (Cloudflare, Imperva, AI/ML like Fastly) |
| **Encryption/Auth** | AES-256/HMAC challenge-response | Secures attacker; prevents hijacks |
| **Modular Config** | Env/GET encrypted params, C2 integration | Adaptable for beacons, exfil modules |
| **Performance Opts** | Dynamic buffers, heartbeats, signal cleanup | Handles edges; low CPU in long sessions |
| **Error Handling** | Try-catch, encrypted diags, non-int fallback | Reliable in restricted (SELinux, disabled funcs) |
| **Anti-Forensics** | Log suppression, self-delete, no writes | Evades EDR/AV (CrowdStrike, SentinelOne) |
| **Educational Comments** | Detailed explanations per section | Teaches tactics for red team excellence |

🧠 Use Cases
- 🧪 HTB Pro Labs / THM Advanced Rooms: Bypass WAFs in web vulns.
- 🕵️ OSCP Exam Scenarios: Stable shells in restricted boxes.
- 🎓 Red Team Sims / CTFs: Test AI evasion, persistence.
- 🔬 Production Pentesting: Authorized RCE testing (with permission!).

⚙️ Setup
1. Set $ip/$port to your listener (e.g., nc -lvnp 443).
2. For HTTP fallback, setup C2 server handling polls.
3. Upload via vuln (file upload/RCE), access URL to trigger.
4. Obfuscate further: Wrap in eval(base64_decode(...)) for deployment.
5. Test in VM: Simulate WAF with ModSecurity.

🧾 License & Ethics
MIT for ethical use only. No unauthorized deployment. Stay ethical – red team for good!

Thanks , Stay Ethical use Proper Responsibility.
