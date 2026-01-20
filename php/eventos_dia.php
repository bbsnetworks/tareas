<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

try {
    $date = $_GET['date'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Fecha inválida. Usa YYYY-MM-DD"]);
        exit;
    }

    $startDay = $date . ' 00:00:00';
    $endDay   = $date . ' 23:59:59';

    // ✅ SOLO filtra por la fecha de `start`
    $sql = "
        SELECT
            id, title, `start`, `end`, color, location, estado, lat, lng,
            evidencia, categoria, comentarios
        FROM eventos
        WHERE `start` >= ?
          AND `start` <= ?
        ORDER BY `start` ASC
    ";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception("Error prepare: " . $conexion->error);

    $stmt->bind_param("ss", $startDay, $endDay);
    $stmt->execute();
    $res = $stmt->get_result();

    $events = [];
    while ($row = $res->fetch_assoc()) {
        $events[] = [
            "id"    => (int)$row["id"],
            "title" => $row["title"],
            "start" => $row["start"],
            "end"   => $row["end"] ?: null,
            "backgroundColor" => $row["color"] ?: "#38bdf8",
            "borderColor"     => $row["color"] ?: "#38bdf8",
            "extendedProps" => [
                "location"    => $row["location"] ?? "",
                "estado"      => $row["estado"] ?? "",
                "lat"         => $row["lat"] ?? null,
                "lng"         => $row["lng"] ?? null,
                "evidencia"   => $row["evidencia"] ?? "",
                "categoria"   => $row["categoria"] ?? "",
                "comentarios" => $row["comentarios"] ?? "",
                "tipo"        => (strtolower($row["categoria"] ?? "") === "vacaciones") ? "vacaciones" : "evento",
            ],
        ];
    }

    echo json_encode(["success" => true, "events" => $events], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
