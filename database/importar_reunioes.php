<?php
/**
 * Script para importar reuniões de células
 * Converte "X" para 0 e popula o banco de dados
 */

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Src\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$config = require __DIR__ . '/../config/config.php';

use Src\Core\Database;

$db = Database::getConnection($config['db']);

// Função para converter X para 0
function parseValue($value) {
    $value = trim($value);
    if (strtoupper($value) === 'X' || $value === '' || $value === null) {
        return 0;
    }
    return (int)$value;
}

// Função para converter oferta
function parseOferta($value) {
    $value = trim($value);
    if (strtoupper($value) === 'X' || $value === '' || $value === null || $value === '0,00' || $value === '0.00') {
        return 0.00;
    }
    // Remove "reais", espaços e converte vírgula para ponto
    $value = str_ireplace(['reais', 'reais', ' '], '', $value);
    $value = str_replace(',', '.', $value);
    return (float)$value;
}

// Função para converter data
function parseData($dataStr, $diaSemana) {
    // Formato: 01/11/25 SÁBADO
    $parts = explode(' ', trim($dataStr));
    $dataPart = $parts[0]; // 01/11/25
    list($dia, $mes, $ano) = explode('/', $dataPart);
    $ano = '20' . $ano; // 25 -> 2025
    return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
}

// Função para converter horário
function parseHorario($horarioStr) {
    // Formato: "20 HORAS" ou "20:00"
    $horarioStr = trim($horarioStr);
    if (preg_match('/(\d{1,2})\s*HORAS?/i', $horarioStr, $matches)) {
        return sprintf('%02d:00:00', (int)$matches[1]);
    }
    if (preg_match('/(\d{1,2}):(\d{2})/', $horarioStr, $matches)) {
        return sprintf('%02d:%02d:00', (int)$matches[1], (int)$matches[2]);
    }
    return '20:00:00'; // padrão
}

// Função para obter dia da semana
function getDiaSemana($dataStr) {
    $dias = [
        'DOMINGO' => 'Domingo',
        'SEGUNDA' => 'Segunda',
        'SEGUNDA-FEIRA' => 'Segunda',
        'TERÇA' => 'Terça',
        'TERÇA-FEIRA' => 'Terça',
        'QUARTA' => 'Quarta',
        'QUARTA-FEIRA' => 'Quarta',
        'QUINTA' => 'Quinta',
        'QUINTA-FEIRA' => 'Quinta',
        'SEXTA' => 'Sexta',
        'SEXTA-FEIRA' => 'Sexta',
        'SÁBADO' => 'Sábado',
        'SABADO' => 'Sábado',
    ];
    
    foreach ($dias as $key => $value) {
        if (stripos($dataStr, $key) !== false) {
            return $value;
        }
    }
    return '';
}

// Função para criar ou obter congregação
function getOrCreateCongregacao($db, $nome) {
    $stmt = $db->prepare('SELECT id FROM congregacoes WHERE nome = :nome LIMIT 1');
    $stmt->execute(['nome' => $nome]);
    $congregacao = $stmt->fetch();
    
    if ($congregacao) {
        return $congregacao['id'];
    }
    
    $stmt = $db->prepare('INSERT INTO congregacoes (nome, ativa) VALUES (:nome, 1)');
    $stmt->execute(['nome' => $nome]);
    return (int)$db->lastInsertId();
}

// Função para criar ou obter usuário e líder
function getOrCreateLider($db, $nomeLider, $telefone) {
    // Primeiro, verificar se já existe líder com esse nome
    $stmt = $db->prepare('SELECT l.id, l.usuario_id, l.telefone FROM lideres l WHERE l.nome = :nome LIMIT 1');
    $stmt->execute(['nome' => $nomeLider]);
    $lider = $stmt->fetch();
    
    if ($lider) {
        // Atualizar telefone se necessário
        $telefoneAtual = $lider['telefone'] ?? null;
        if ($telefone && !$telefoneAtual) {
            $stmt = $db->prepare('UPDATE lideres SET telefone = :telefone WHERE id = :id');
            $stmt->execute(['telefone' => $telefone, 'id' => $lider['id']]);
        }
        return $lider['id'];
    }
    
    // Criar usuário
    $email = strtolower(str_replace([' ', 'E', 'E'], ['', '', ''], $nomeLider)) . '@igreja.com';
    $email = preg_replace('/[^a-z0-9@.]/', '', $email);
    
    // Verificar se email já existe
    $stmt = $db->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $senha = password_hash('lider123', PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO usuarios (nome, email, senha, perfil, ativo, data_criacao) VALUES (:nome, :email, :senha, "lider", 1, NOW())');
        $stmt->execute([
            'nome' => $nomeLider,
            'email' => $email,
            'senha' => $senha
        ]);
        $usuarioId = (int)$db->lastInsertId();
    } else {
        $usuarioId = (int)$usuario['id'];
    }
    
    // Criar líder
    $stmt = $db->prepare('INSERT INTO lideres (nome, telefone, usuario_id) VALUES (:nome, :telefone, :usuario_id)');
    $stmt->execute([
        'nome' => $nomeLider,
        'telefone' => $telefone ?: null,
        'usuario_id' => $usuarioId
    ]);
    
    return (int)$db->lastInsertId();
}

// Função para criar ou obter célula
function getOrCreateCelula($db, $nomeCelula, $congregacaoId, $liderId) {
    $stmt = $db->prepare('SELECT id FROM celulas WHERE nome = :nome LIMIT 1');
    $stmt->execute(['nome' => $nomeCelula]);
    $celula = $stmt->fetch();
    
    if ($celula) {
        // Atualizar líder se necessário
        if ($liderId) {
            $stmt = $db->prepare('UPDATE celulas SET lider_id = :lider_id WHERE id = :id');
            $stmt->execute(['lider_id' => $liderId, 'id' => $celula['id']]);
        }
        return $celula['id'];
    }
    
    $stmt = $db->prepare('INSERT INTO celulas (nome, congregacao_id, lider_id, ativa) VALUES (:nome, :congregacao_id, :lider_id, 1)');
    $stmt->execute([
        'nome' => $nomeCelula,
        'congregacao_id' => $congregacaoId,
        'lider_id' => $liderId
    ]);
    
    return (int)$db->lastInsertId();
}

// Dados das reuniões
$reunioes = [
    // ATOS 29
    ['celula' => 'ATOS 29', 'lider' => 'IVAN E MARCELA', 'anfitriao' => 'IVAN E MARCELA', 'telefone' => '(65)98128-7629', 'cadastrados' => 6, 'presentes' => 5, 'visitantes' => 1, 'mda' => 'X', 'visitas' => 'X', 'culto' => 'X', 'aceitacao' => 'X', 'oferta' => '4,00 reais', 'data' => '01/11/25 SÁBADO', 'horario' => '20 HORAS'],
    ['celula' => 'ATOS 29', 'lider' => 'IVAN E MARCELA', 'anfitriao' => 'IVAN E MARCELA', 'telefone' => '(65)98128-7629', 'cadastrados' => 6, 'presentes' => 6, 'visitantes' => 1, 'mda' => 'X', 'visitas' => 'X', 'culto' => 'X', 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '08/11/25 SÁBADO', 'horario' => '20 HORAS'],
    ['celula' => 'ATOS 29', 'lider' => 'IVAN E MARCELA', 'anfitriao' => 'IVAN E MARCELA', 'telefone' => '(65)98128-7629', 'cadastrados' => 6, 'presentes' => 6, 'visitantes' => 1, 'mda' => 'X', 'visitas' => 'X', 'culto' => 'X', 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '15/11/25 SÁBADO', 'horario' => '20 HORAS'],
    ['celula' => 'ATOS 29', 'lider' => 'IVAN E MARCELA', 'anfitriao' => 'IVAN E MARCELA', 'telefone' => '(65)98128-7629', 'cadastrados' => 6, 'presentes' => 5, 'visitantes' => 1, 'mda' => 'X', 'visitas' => 'X', 'culto' => 'X', 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '22/11/25 SÁBADO', 'horario' => '20 HORAS'],
    ['celula' => 'ATOS 29', 'lider' => 'IVAN E MARCELA', 'anfitriao' => 'IVAN E MARCELA', 'telefone' => '(65)98128-7629', 'cadastrados' => 6, 'presentes' => 4, 'visitantes' => 1, 'mda' => 'X', 'visitas' => 'X', 'culto' => 'X', 'aceitacao' => 'X', 'oferta' => '11,80 reais', 'data' => '29/11/25 SÁBADO', 'horario' => '20 HORAS'],
    
    // TRIBO DE JUDÁ
    ['celula' => 'TRIBO DE JUDÁ', 'lider' => 'JOSÉ MORAES E CLÉIA', 'anfitriao' => 'JOSÉ MORAES E CLÉIA', 'telefone' => '99301-5761', 'cadastrados' => 13, 'presentes' => 9, 'visitantes' => 'X', 'mda' => 'X', 'visitas' => 'X', 'culto' => 5, 'aceitacao' => 'X', 'oferta' => '17,00 Reais', 'data' => '05/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'TRIBO DE JUDÁ', 'lider' => 'JOSÉ MORAES E CLÉIA', 'anfitriao' => 'JOSÉ MORAES E CLÉIA', 'telefone' => '99301-5761', 'cadastrados' => 13, 'presentes' => 8, 'visitantes' => 2, 'mda' => 'X', 'visitas' => 'X', 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '7,00 Reais', 'data' => '12/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'TRIBO DE JUDÁ', 'lider' => 'JOSÉ MORAES E CLÉIA', 'anfitriao' => 'JOSÉ MORAES E CLÉIA', 'telefone' => '99301-5761', 'cadastrados' => 13, 'presentes' => 4, 'visitantes' => 3, 'mda' => 'X', 'visitas' => 'X', 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '9,00 Reais', 'data' => '19/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'TRIBO DE JUDÁ', 'lider' => 'JOSÉ MORAES E CLÉIA', 'anfitriao' => 'JOSÉ MORAES E CLÉIA', 'telefone' => '99301-5761', 'cadastrados' => 13, 'presentes' => 6, 'visitantes' => 1, 'mda' => 'X', 'visitas' => 'X', 'culto' => 6, 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '26/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    
    // ATALAIA
    ['celula' => 'ATALAIA', 'lider' => 'ALESSANDRO E DANIELLE', 'anfitriao' => 'ALESSANDRO E DANIELLE', 'telefone' => '99292-9501', 'cadastrados' => 8, 'presentes' => 8, 'visitantes' => 2, 'mda' => 4, 'visitas' => 2, 'culto' => 6, 'aceitacao' => 'X', 'oferta' => '15,00', 'data' => '05/11/25 QUARTA-FEIRA', 'horario' => '19 HORAS'],
    ['celula' => 'ATALAIA', 'lider' => 'ALESSANDRO E DANIELLE', 'anfitriao' => 'ALESSANDRO E DANIELLE', 'telefone' => '99292-9501', 'cadastrados' => 8, 'presentes' => 8, 'visitantes' => 1, 'mda' => 3, 'visitas' => 'X', 'culto' => 6, 'aceitacao' => 'X', 'oferta' => '12,00', 'data' => '12/11/25 QUARTA-FEIRA', 'horario' => '19 HORAS'],
    ['celula' => 'ATALAIA', 'lider' => 'ALESSANDRO E DANIELLE', 'anfitriao' => 'ALESSANDRO E DANIELLE', 'telefone' => '99292-9501', 'cadastrados' => 8, 'presentes' => 8, 'visitantes' => 2, 'mda' => 2, 'visitas' => 'X', 'culto' => 6, 'aceitacao' => 'X', 'oferta' => '10,00', 'data' => '19/11/25 QUARTA-FEIRA', 'horario' => '19 HORAS'],
    ['celula' => 'ATALAIA', 'lider' => 'ALESSANDRO E DANIELLE', 'anfitriao' => 'ALESSANDRO E DANIELLE', 'telefone' => '99292-9501', 'cadastrados' => 8, 'presentes' => 7, 'visitantes' => 1, 'mda' => 2, 'visitas' => 'X', 'culto' => 6, 'aceitacao' => 'X', 'oferta' => '8,00', 'data' => '26/11/25 QUARTA-FEIRA', 'horario' => '19 HORAS'],
    
    // ESTRELA DA MANHÃ
    ['celula' => 'ESTRELA DA MANHÃ', 'lider' => 'MIRLENE', 'anfitriao' => 'MIRLENE', 'telefone' => '99371-4696', 'cadastrados' => 11, 'presentes' => 9, 'visitantes' => 3, 'mda' => 'X', 'visitas' => 1, 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '05/11/25 QUARTA-FEIRA', 'horario' => '18 HORAS'],
    ['celula' => 'ESTRELA DA MANHÃ', 'lider' => 'MIRLENE', 'anfitriao' => 'MIRLENE', 'telefone' => '99371-4696', 'cadastrados' => 11, 'presentes' => 9, 'visitantes' => 'X', 'mda' => 'X', 'visitas' => 1, 'culto' => 10, 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '12/11/25 QUARTA-FEIRA', 'horario' => '18 HORAS'],
    ['celula' => 'ESTRELA DA MANHÃ', 'lider' => 'MIRLENE', 'anfitriao' => 'MIRLENE', 'telefone' => '99371-4696', 'cadastrados' => 11, 'presentes' => 7, 'visitantes' => 2, 'mda' => 'X', 'visitas' => 2, 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '19/11/25 QUARTA-FEIRA', 'horario' => '18 HORAS'],
    ['celula' => 'ESTRELA DA MANHÃ', 'lider' => 'MIRLENE', 'anfitriao' => 'MIRLENE', 'telefone' => '99371-4696', 'cadastrados' => 11, 'presentes' => 8, 'visitantes' => 1, 'mda' => 'X', 'visitas' => 1, 'culto' => 8, 'aceitacao' => 'X', 'oferta' => '0,00', 'data' => '26/11/25 QUARTA-FEIRA', 'horario' => '18 HORAS'],
    
    // SENTINELAS
    ['celula' => 'SENTINELAS', 'lider' => 'JADSON E SIMONY', 'anfitriao' => 'MACD MARAVILHA', 'telefone' => '99297-7511', 'cadastrados' => 8, 'presentes' => 8, 'visitantes' => 2, 'mda' => 1, 'visitas' => 'X', 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '17,00 Reais', 'data' => '05/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'SENTINELAS', 'lider' => 'JADSON E SIMONY', 'anfitriao' => 'MACD MARAVILHA', 'telefone' => '99297-7511', 'cadastrados' => 8, 'presentes' => 8, 'visitantes' => 5, 'mda' => 1, 'visitas' => 'X', 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '25,35 Reais', 'data' => '12/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'SENTINELAS', 'lider' => 'JADSON E SIMONY', 'anfitriao' => 'MACD MARAVILHA', 'telefone' => '99297-7511', 'cadastrados' => 8, 'presentes' => 7, 'visitantes' => 2, 'mda' => 2, 'visitas' => 'X', 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '15,00', 'data' => '19/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'SENTINELAS', 'lider' => 'JADSON E SIMONY', 'anfitriao' => 'MACD MARAVILHA', 'telefone' => '99297-7511', 'cadastrados' => 8, 'presentes' => 8, 'visitantes' => 3, 'mda' => 1, 'visitas' => 'X', 'culto' => 7, 'aceitacao' => 'X', 'oferta' => '20,00', 'data' => '26/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    
    // RESGATE
    ['celula' => 'RESGATE', 'lider' => 'MARCOS E PAULA', 'anfitriao' => 'MARCOS E PAULA', 'telefone' => '99225-2626', 'cadastrados' => 11, 'presentes' => 10, 'visitantes' => 2, 'mda' => 5, 'visitas' => 'X', 'culto' => 11, 'aceitacao' => 'X', 'oferta' => '47,00 Reais', 'data' => '05/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'RESGATE', 'lider' => 'MARCOS E PAULA', 'anfitriao' => 'MARCOS E PAULA', 'telefone' => '99225-2626', 'cadastrados' => 11, 'presentes' => 10, 'visitantes' => 2, 'mda' => 4, 'visitas' => 'X', 'culto' => 11, 'aceitacao' => 1, 'oferta' => '50,50 Reais', 'data' => '12/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'RESGATE', 'lider' => 'MARCOS E PAULA', 'anfitriao' => 'MARCOS E PAULA', 'telefone' => '99225-2626', 'cadastrados' => 11, 'presentes' => 11, 'visitantes' => 1, 'mda' => 2, 'visitas' => 'X', 'culto' => 11, 'aceitacao' => 'X', 'oferta' => '35,00', 'data' => '19/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
    ['celula' => 'RESGATE', 'lider' => 'MARCOS E PAULA', 'anfitriao' => 'MARCOS E PAULA', 'telefone' => '99225-2626', 'cadastrados' => 11, 'presentes' => 10, 'visitantes' => 2, 'mda' => 3, 'visitas' => 'X', 'culto' => 11, 'aceitacao' => 1, 'oferta' => '45,00', 'data' => '26/11/25 QUARTA-FEIRA', 'horario' => '20 HORAS'],
];

try {
    $db->beginTransaction();
    
    // Criar congregação
    $congregacaoId = getOrCreateCongregacao($db, 'MACD MARAVILHA II');
    
    // Obter admin para criado_por
    $stmt = $db->query("SELECT id FROM usuarios WHERE perfil = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    $criadoPor = $admin ? $admin['id'] : 1;
    
    $importadas = 0;
    $ignoradas = 0;
    
    foreach ($reunioes as $reuniao) {
        // Criar líder
        $liderId = getOrCreateLider($db, $reuniao['lider'], $reuniao['telefone']);
        
        // Criar célula
        $celulaId = getOrCreateCelula($db, $reuniao['celula'], $congregacaoId, $liderId);
        
        // Processar dados
        $data = parseData($reuniao['data'], '');
        $diaSemana = getDiaSemana($reuniao['data']);
        $horario = parseHorario($reuniao['horario']);
        
        // Verificar se já existe
        $stmt = $db->prepare('SELECT id FROM reunioes WHERE celula_id = :celula_id AND data = :data AND horario = :horario LIMIT 1');
        $stmt->execute([
            'celula_id' => $celulaId,
            'data' => $data,
            'horario' => $horario
        ]);
        
        if ($stmt->fetch()) {
            $ignoradas++;
            continue;
        }
        
        // Inserir reunião
        $stmt = $db->prepare('
            INSERT INTO reunioes (
                celula_id, nome_celula, lider_nome, anfitriao_nome, telefone_lider,
                data, dia_semana, horario, cadastrados, presentes, visitantes, mda,
                visitas, culto_celebracao, aceitacao, oferta, criado_por, criado_em
            ) VALUES (
                :celula_id, :nome_celula, :lider_nome, :anfitriao_nome, :telefone_lider,
                :data, :dia_semana, :horario, :cadastrados, :presentes, :visitantes, :mda,
                :visitas, :culto_celebracao, :aceitacao, :oferta, :criado_por, NOW()
            )
        ');
        
        $stmt->execute([
            'celula_id' => $celulaId,
            'nome_celula' => $reuniao['celula'],
            'lider_nome' => $reuniao['lider'],
            'anfitriao_nome' => $reuniao['anfitriao'],
            'telefone_lider' => $reuniao['telefone'],
            'data' => $data,
            'dia_semana' => $diaSemana,
            'horario' => $horario,
            'cadastrados' => parseValue($reuniao['cadastrados']),
            'presentes' => parseValue($reuniao['presentes']),
            'visitantes' => parseValue($reuniao['visitantes']),
            'mda' => parseValue($reuniao['mda']),
            'visitas' => parseValue($reuniao['visitas']),
            'culto_celebracao' => parseValue($reuniao['culto']),
            'aceitacao' => parseValue($reuniao['aceitacao']),
            'oferta' => parseOferta($reuniao['oferta']),
            'criado_por' => $criadoPor
        ]);
        
        $importadas++;
    }
    
    $db->commit();
    
    echo "✅ Importação concluída!\n";
    echo "📊 Reuniões importadas: {$importadas}\n";
    echo "⏭️  Reuniões ignoradas (duplicadas): {$ignoradas}\n";
    echo "🏢 Congregação: MACD MARAVILHA II\n";
    echo "📅 Período: Novembro 2025\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Erro na importação: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}

