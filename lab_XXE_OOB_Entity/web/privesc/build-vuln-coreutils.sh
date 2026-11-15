#!/bin/bash
set -euo pipefail

cd /tmp

# CVE-2026-35368: uutils/coreutils <= 0.8.0
# Build only the chroot utility. This is intentionally vulnerable.
# Debian Trixie ships Rust 1.85.x, which is too old for uutils 0.8.0.
# Use a pinned Rust 1.88 toolchain so the vulnerable source can still be built.
apt-get update
apt-get install -y --no-install-recommends \
    build-essential curl pkg-config libssl-dev git ca-certificates

rm -rf /tmp/uutils-coreutils

# Install rustup without modifying the user's shell configuration.
export RUSTUP_HOME=/opt/rustup
export CARGO_HOME=/opt/cargo
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs \
    | sh -s -- -y --profile minimal --default-toolchain 1.88.0
export PATH="$CARGO_HOME/bin:$PATH"

rustc --version
cargo --version

# Pin the vulnerable release by tag and verify the expected commit.
git clone --depth 1 --branch 0.8.0 https://github.com/uutils/coreutils.git /tmp/uutils-coreutils
cd /tmp/uutils-coreutils

test "$(git rev-parse HEAD)" = "c4093734e2ebe2efb7d65e216cd1444664bcf26a"

cargo build --release --locked -p uu_chroot

install -o root -g root -m 4755 target/release/chroot /usr/local/bin/lab-chroot

# Keep build artifacts and the compiler out of the final image.
rm -rf /tmp/uutils-coreutils /opt/rustup /opt/cargo
apt-get purge -y build-essential curl pkg-config libssl-dev git ca-certificates
apt-get autoremove -y
apt-get clean
rm -rf /var/lib/apt/lists/*

# Make a writable challenge root owned by jack.
mkdir -p /opt/lab/chroot/etc /opt/lab/chroot/lib /opt/lab/chroot/lib64 /opt/lab/chroot/usr
chown -R jack:jack /opt/lab/chroot
chmod 755 /opt/lab/chroot

cat > /opt/lab/chroot/etc/passwd <<'PASSWD'
root:x:0:0:root:/root:/bin/sh
jack:x:1000:1000:jack:/home/jack:/bin/bash
PASSWD

cat > /opt/lab/chroot/etc/group <<'GROUP'
root:x:0:
jack:x:1000:
GROUP

cat > /opt/lab/chroot/etc/nsswitch.conf <<'NSS'
passwd: demo
group: files
shadow: files
hosts: files
NSS

chown -R jack:jack /opt/lab/chroot
