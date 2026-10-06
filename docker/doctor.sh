#!/bin/sh
# Pre-flight check for the Rampart sandbox: Docker, Compose, the four images, and port
# 8080 — then prints the one command to run next. Read-only: it never pulls, builds, or
# starts anything, so it's safe to run anywhere, any time. `make doctor` runs this; so
# does `sh docker/doctor.sh` on a machine without make.

cd "$(dirname "$0")/.." || exit 1

APP_IMAGES="rampart-app rampart-metadata-mock"
BASE_IMAGES=${BASE_IMAGES:-"mysql:8.4 redis:7-alpine"}
BUNDLE=rampart-images.tar.gz
PORT=8080
MIN_ENGINE=24
MIN_MEM_GB=2

# Files baked into rampart-app that matter at runtime. A change here since the image was
# built means the container is running old code.
APP_PATHSPEC=". :(exclude)docs :(exclude)*.md :(exclude)Makefile :(exclude).github :(exclude)mock :(exclude)docker/doctor.sh"

if [ -t 1 ] && [ -z "${NO_COLOR:-}" ]; then
    green=$(printf '\033[32m'); yellow=$(printf '\033[33m'); red=$(printf '\033[31m')
    bold=$(printf '\033[1m'); reset=$(printf '\033[0m')
else
    green=; yellow=; red=; bold=; reset=
fi

failures=0
warnings=0
next=""

section() { printf '\n%s%s%s\n' "$bold" "$1" "$reset"; }
ok()      { printf '  %s✓%s %s\n' "$green" "$reset" "$1"; }
warn()    { printf '  %s!%s %s\n' "$yellow" "$reset" "$1"; warnings=$((warnings + 1)); }
fail()    { printf '  %s✗%s %s\n' "$red" "$reset" "$1"; failures=$((failures + 1)); }
hint()    { printf '      %s\n' "$1"; }
# First suggestion wins: checks run most-fundamental first, so that's the one to fix first.
suggest() { [ -n "$next" ] || next=$1; }

finish() {
    section "Next step"
    if [ "$failures" -eq 0 ] && [ "$warnings" -eq 0 ]; then
        printf '  %sAll set.%s Run: %smake up%s  (or: docker compose up)\n' "$green" "$reset" "$bold" "$reset"
        printf '  Then open http://localhost:%s — first boot seeds the database, give it a minute.\n\n' "$PORT"
        exit 0
    fi
    [ -n "$next" ] || next="Fix the items marked above."
    printf '  %s\n' "$next"
    if [ "$failures" -eq 0 ]; then
        printf '  (Warnings only — %smake up%s should still work.)\n\n' "$bold" "$reset"
        exit 0
    fi
    printf '  Then re-run %smake doctor%s to confirm.\n\n' "$bold" "$reset"
    exit 1
}

# --- Docker ---------------------------------------------------------------------------

section "Docker"

if ! command -v docker >/dev/null 2>&1; then
    fail "docker is not installed (not on your PATH)"
    hint "macOS/Windows: install Docker Desktop. Linux: install Docker Engine."
    hint "https://docs.docker.com/get-docker/"
    suggest "Install Docker and start it."
    finish
fi

if ! engine=$(docker version --format '{{.Server.Version}}' 2>&1); then
    case $engine in
        *"permission denied"*)
            fail "docker is installed, but you don't have permission to use it"
            hint "Linux: sudo usermod -aG docker \$USER, then log out and back in."
            suggest "Fix Docker permissions." ;;
        *)
            fail "docker is installed, but the Docker daemon isn't running"
            hint "Start Docker Desktop (or: sudo systemctl start docker) and wait until it's ready."
            suggest "Start Docker." ;;
    esac
    finish
fi

major=${engine%%.*}
case $major in
    ''|*[!0-9]*) ok "Docker Engine $engine" ;;
    *) if [ "$major" -ge "$MIN_ENGINE" ]; then
           ok "Docker Engine $engine"
       else
           warn "Docker Engine $engine is older than the supported $MIN_ENGINE.0"
           hint "Update Docker Desktop / Docker Engine if anything below misbehaves."
       fi ;;
esac

if compose=$(docker compose version --short 2>/dev/null); then
    compose=${compose#v}
    case ${compose%%.*} in
        0|1) fail "Docker Compose $compose is too old (need v2+)"
             suggest "Update Docker so 'docker compose' is v2+." ;;
        *)   ok "Docker Compose $compose" ;;
    esac
else
    fail "the 'docker compose' plugin isn't available"
    if command -v docker-compose >/dev/null 2>&1; then
        hint "You have the old standalone 'docker-compose' (v1); Rampart needs 'docker compose' (v2)."
    fi
    hint "https://docs.docker.com/compose/install/"
    suggest "Install the Docker Compose v2 plugin."
fi

mem=$(docker info --format '{{.MemTotal}}' 2>/dev/null)
case $mem in
    ''|*[!0-9]*) ;;
    *) mem_gb=$(awk -v b="$mem" 'BEGIN { printf "%.1f", b / 1073741824 }')
       if awk -v b="$mem" -v min="$MIN_MEM_GB" 'BEGIN { exit !(b < min * 1073741824 * 0.95) }'; then
           warn "Docker can use only ${mem_gb} GB of memory (want ${MIN_MEM_GB}+ GB)"
           hint "Docker Desktop → Settings → Resources → Memory."
       else
           ok "${mem_gb} GB of memory available to Docker"
       fi ;;
esac

# `docker info` says aarch64/x86_64; image metadata says arm64/amd64.
case $(docker info --format '{{.Architecture}}' 2>/dev/null) in
    aarch64|arm64) engine_arch=arm64 ;;
    x86_64|amd64)  engine_arch=amd64 ;;
    *)             engine_arch= ;;
esac

# --- Images ---------------------------------------------------------------------------

section "Images"

missing=0
wrong_arch=""
for img in $APP_IMAGES $BASE_IMAGES; do
    if ! arch=$(docker image inspect --format '{{.Architecture}}' "$img" 2>/dev/null); then
        fail "$img — not present"
        missing=$((missing + 1))
    elif [ -n "$engine_arch" ] && [ "$arch" != "$engine_arch" ]; then
        if [ "$engine_arch" = arm64 ] && [ "$arch" = amd64 ]; then
            # Apple Silicon runs these under emulation: works, just slower.
            warn "$img — built for $arch; runs under emulation on this $engine_arch machine (slower)"
        else
            fail "$img — built for $arch, but this machine is $engine_arch"
        fi
        wrong_arch="$wrong_arch $img"
    else
        ok "$img"
    fi
done

if [ -n "$wrong_arch" ]; then
    hint "These images came from a machine with a different CPU; build your own instead."
    # Removing them first matters: `make pull` skips any base image that's already present.
    suggest "Run: docker image rm$wrong_arch && make build   (needs internet, ~5–10 min)"
elif [ "$missing" -gt 0 ]; then
    if [ -f "$BUNDLE" ]; then
        hint "Found $BUNDLE here — loading it needs no network."
        suggest "Run: make load"
    else
        hint "Building downloads the PHP/Node/MySQL/Redis base images plus dependencies,"
        hint "so do it on a good connection — not on conference wifi."
        suggest "Run: make build   (needs internet, ~5–10 min)"
    fi
fi

# Is the app image older than the code in this checkout? Only answerable for images built
# via `make build`/`make dist`, which stamp the git revision into a label.
rev=$(docker image inspect --format '{{index .Config.Labels "rampart.revision"}}' rampart-app 2>/dev/null)
case $rev in
    ''|unknown|'<no value>') ;;
    *) if command -v git >/dev/null 2>&1 && git rev-parse --git-dir >/dev/null 2>&1; then
           short=$(printf '%s' "$rev" | cut -c1-7)
           if ! git cat-file -e "$rev^{commit}" 2>/dev/null; then
               warn "rampart-app was built from commit $short, which isn't in your checkout"
               hint "Your clone is probably out of date."
               suggest "Run: git pull && make build"
           # shellcheck disable=SC2086  # APP_PATHSPEC is deliberately word-split
           elif ! git diff --quiet "$rev" HEAD -- $APP_PATHSPEC; then
               warn "rampart-app was built from $short; the app code has changed since"
               hint "The container would run the old code until you rebuild."
               suggest "Run: make build"
           else
               ok "rampart-app matches your checkout ($short)"
           fi
       fi ;;
esac

# --- Port -----------------------------------------------------------------------------

section "Port $PORT"

if docker compose ps --status running --services 2>/dev/null | grep -qx app; then
    ok "Rampart is already running — http://localhost:$PORT"
    [ "$failures" -eq 0 ] && [ "$warnings" -eq 0 ] && {
        printf '\n  Nothing to do. Stop it with: make down\n\n'
        exit 0
    }
else
    busy=
    if command -v lsof >/dev/null 2>&1; then
        lsof -nP -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1 && busy=1
    elif command -v nc >/dev/null 2>&1; then
        nc -z 127.0.0.1 "$PORT" >/dev/null 2>&1 && busy=1
    fi
    if [ -n "$busy" ]; then
        fail "something else is already listening on port $PORT"
        command -v lsof >/dev/null 2>&1 && hint "See what: lsof -nP -iTCP:$PORT -sTCP:LISTEN"
        suggest "Stop whatever is using port $PORT."
    else
        ok "port $PORT is free"
    fi
fi

finish
