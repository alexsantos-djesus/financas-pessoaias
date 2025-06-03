function getCategorias($pdo, $usuario_id) {
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
return $stmt->fetchAll(PDO::FETCH_ASSOC);
}