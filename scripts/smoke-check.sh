#!/usr/bin/env bash

set -euo pipefail

base_url="${1:-http://127.0.0.1}"

check_route() {
    local path="$1"
    local expected_status="$2"
    local url="${base_url}${path}"
    local status

    status="$(curl -s -o /dev/null -w '%{http_code}' --retry 10 --retry-delay 3 --retry-connrefused "$url")"

    if [[ "$status" != "$expected_status" ]]; then
        echo "Smoke check failed for ${path}: expected ${expected_status}, got ${status}" >&2
        exit 1
    fi

    echo "${path} -> ${status}"
}

check_route / 200
check_route /login 200
check_route /register 200
check_route /dashboard 302
check_route /projects 302
