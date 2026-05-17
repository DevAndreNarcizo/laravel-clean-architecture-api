#!/usr/bin/env bash
# Testes Automatizados — Clean Architecture API
# Requer: bru CLI (npm install -g @usebruno/cli)
set -euo pipefail

COLLECTION="/var/www/laravel-clean-architecture-api/bruno"
BASE_URL="http://localhost:8080/api/v1"
EMAIL="test@example.com"
PASSWORD="password"
REPORTS="$COLLECTION/reports"
PASS=0
FAIL=0
PASSED_ASSERTIONS=0
TOTAL_ASSERTIONS=0
PROJECT_ID=""
TASK_ID=""

mkdir -p "$REPORTS"

section() { echo ""; echo "--- $1 ---"; }

run_phase() {
  local phase_name="$1"
  local output_file="$2"
  shift 2

  section "$phase_name"

  local env_args=()
  env_args+=("--env-var" "base_url=$BASE_URL")
  env_args+=("--env-var" "email=$EMAIL")
  env_args+=("--env-var" "password=$PASSWORD")
  env_args+=("--env-var" "project_id=$PROJECT_ID")
  env_args+=("--env-var" "task_id=$TASK_ID")
  [ -n "${TOKEN:-}" ] && env_args+=("--env-var" "auth_token=$TOKEN")
  [ -n "${REFRESH_TOKEN:-}" ] && env_args+=("--env-var" "refresh_token=$REFRESH_TOKEN")

  cd "$COLLECTION" && bru run "$@" "${env_args[@]}" --output "$REPORTS/$output_file" 2>&1 || true

  if [ -f "$REPORTS/$output_file" ]; then
    local total_reqs=$(jq -r '.[0].summary.totalRequests // 0' "$REPORTS/$output_file" 2>/dev/null || echo "0")
    local passed_reqs=$(jq -r '.[0].summary.passedRequests // 0' "$REPORTS/$output_file" 2>/dev/null || echo "0")
    local failed_reqs=$(jq -r '.[0].summary.failedRequests // 0' "$REPORTS/$output_file" 2>/dev/null || echo "0")
    local passed_assert=$(jq -r '.[0].summary.passedAssertions // 0' "$REPORTS/$output_file" 2>/dev/null || echo "0")
    local total_assert=$(jq -r '.[0].summary.totalAssertions // 0' "$REPORTS/$output_file" 2>/dev/null || echo "0")

    if [ "$failed_reqs" = "0" ] && [ "$total_reqs" -gt "0" ]; then PASS=$((PASS + 1)); else FAIL=$((FAIL + 1)); fi
    PASSED_ASSERTIONS=$((PASSED_ASSERTIONS + passed_assert))
    TOTAL_ASSERTIONS=$((TOTAL_ASSERTIONS + total_assert))
  fi
}

extract_var() {
  jq -r ".[0].results[0].response.data.data.$1 // \"$2\"" "$REPORTS/$3" 2>/dev/null || echo "$2"
}

# === Fase 1: Login ===
run_phase "Login" "01-login.json" "auth/Login.bru"
TOKEN=$(extract_var "access_token" "" "01-login.json")
REFRESH_TOKEN=$(extract_var "refresh_token" "" "01-login.json")
echo "  -> Token: ${TOKEN:0:30}..."

# === Fase 2: Auth ===
run_phase "Me" "02-auth-me.json" "auth/Me.bru"
run_phase "Refresh" "02-auth-refresh.json" "auth/Refresh.bru"

# === Fase 3: Criar projeto + tarefa ===
run_phase "Create Project" "03-project-create.json" "projects/Create.bru"
PROJECT_ID=$(extract_var "id" "$PROJECT_ID" "03-project-create.json")
echo "  -> project_id=$PROJECT_ID"

run_phase "Show Project" "03-project-show.json" "projects/Show.bru"
run_phase "Update Project" "03-project-update.json" "projects/Update.bru"
run_phase "List Projects" "03-projects-list.json" "projects/List.bru"

run_phase "Create Task" "03-task-create.json" "tasks/Create.bru"
TASK_ID=$(extract_var "id" "$TASK_ID" "03-task-create.json")
echo "  -> task_id=$TASK_ID"

run_phase "Show Task" "03-task-show.json" "tasks/Show.bru"
run_phase "Update Task" "03-task-update.json" "tasks/Update.bru"
run_phase "List Tasks" "03-tasks-list.json" "tasks/List.bru"

# === Fase 4: Limpeza ===
run_phase "Delete Task" "04-task-delete.json" "tasks/Delete.bru"
run_phase "Delete Project" "04-project-delete.json" "projects/Delete.bru"
run_phase "Logout" "04-logout.json" "auth/Logout.bru"

# === Resultado ===
echo ""
echo "============================================"
echo "  RESULTADO FINAL"
echo "============================================"
echo "  Fases:    $((PASS + FAIL)) total, $PASS passaram, $FAIL falharam"
echo "  Assertions: $PASSED_ASSERTIONS/$TOTAL_ASSERTIONS passaram"
echo "============================================"

[ "$FAIL" -eq 0 ] && exit 0 || exit 1
