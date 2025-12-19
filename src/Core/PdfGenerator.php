<?php

namespace Src\Core;

/**
 * Gerador de PDF simples usando TCPDF ou fallback básico
 * Requer TCPDF: baixe de https://github.com/tecnickcom/TCPDF
 * e coloque em vendor/tecnickcom/tcpdf/
 */
class PdfGenerator
{
    private $pdf;
    private bool $tcpdfAvailable = false;

    public function __construct()
    {
        // Tentar carregar TCPDF
        $tcpdfPath = __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
        if (file_exists($tcpdfPath)) {
            require_once $tcpdfPath;
            $this->tcpdfAvailable = true;
            $this->initTcpdf();
        } else {
            // Fallback: criar PDF básico usando funções nativas
            $this->initBasic();
        }
    }

    private function initTcpdf(): void
    {
        $this->pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->pdf->SetCreator('Sistema Gestão de Células');
        $this->pdf->SetAuthor('Igreja');
        $this->pdf->SetTitle('Relatório Mensal');
        $this->pdf->SetSubject('Relatório de Células');
        $this->pdf->SetKeywords('igreja, células, relatório');
        $this->pdf->SetMargins(15, 20, 15);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);
        $this->pdf->SetAutoPageBreak(true, 15);
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $this->pdf->setLanguageArray(['a_meta_charset' => 'UTF-8']);
    }

    private function initBasic(): void
    {
        // Fallback básico - retornará erro se TCPDF não estiver disponível
        $this->tcpdfAvailable = false;
    }

    public function addPage(): void
    {
        if (!$this->tcpdfAvailable) {
            throw new \Exception('TCPDF não está disponível. Por favor, instale TCPDF em vendor/tecnickcom/tcpdf/');
        }
        $this->pdf->AddPage();
    }

    public function setHeader(string $title, string $subtitle = ''): void
    {
        if (!$this->tcpdfAvailable) {
            return;
        }

        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->Cell(0, 10, $title, 0, 1, 'C');
        
        if ($subtitle) {
            $this->pdf->SetFont('helvetica', '', 12);
            $this->pdf->Cell(0, 8, $subtitle, 0, 1, 'C');
        }
        
        $this->pdf->Ln(5);
    }

    public function addSection(string $title, int $fontSize = 12): void
    {
        if (!$this->tcpdfAvailable) {
            return;
        }

        $this->pdf->SetFont('helvetica', 'B', $fontSize);
        $this->pdf->Cell(0, 8, $title, 0, 1, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Ln(2);
    }

    public function addTable(array $headers, array $data): void
    {
        if (!$this->tcpdfAvailable) {
            return;
        }

        // Cabeçalho da tabela
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(230, 230, 230);
        
        $colWidths = $this->calculateColumnWidths(count($headers));
        $rowHeight = 7;

        // Desenhar cabeçalho
        foreach ($headers as $i => $header) {
            $this->pdf->Cell($colWidths[$i], $rowHeight, $header, 1, 0, 'C', true);
        }
        $this->pdf->Ln();

        // Dados
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetFillColor(255, 255, 255);
        
        foreach ($data as $row) {
            $fill = false;
            foreach ($row as $i => $cell) {
                $text = is_numeric($cell) ? number_format((float)$cell, $i >= count($row) - 1 ? 2 : 0, ',', '.') : (string)$cell;
                $this->pdf->Cell($colWidths[$i], $rowHeight, $text, 1, 0, 'L', $fill);
            }
            $this->pdf->Ln();
            $fill = !$fill;
        }
        
        $this->pdf->Ln(5);
    }

    public function addStats(array $stats, array $labels): void
    {
        if (!$this->tcpdfAvailable) {
            return;
        }

        $this->pdf->SetFont('helvetica', 'B', 10);
        $colWidth = 180 / count($labels);
        
        foreach ($labels as $i => $label) {
            $value = $stats[$i] ?? 0;
            $formatted = is_numeric($value) ? number_format((float)$value, is_float($value) ? 2 : 0, ',', '.') : $value;
            
            $this->pdf->Cell($colWidth, 8, $label . ': ' . $formatted, 1, 0, 'C', true);
        }
        $this->pdf->Ln(8);
    }

    public function addText(string $text, int $fontSize = 10, string $align = 'L'): void
    {
        if (!$this->tcpdfAvailable) {
            return;
        }

        $this->pdf->SetFont('helvetica', '', $fontSize);
        $this->pdf->MultiCell(0, 6, $text, 0, $align);
        $this->pdf->Ln(3);
    }

    public function output(string $filename): void
    {
        if (!$this->tcpdfAvailable) {
            throw new \Exception('TCPDF não está disponível');
        }

        $this->pdf->Output($filename, 'D'); // D = download
        exit;
    }

    private function calculateColumnWidths(int $numCols): array
    {
        $totalWidth = 180;
        $baseWidth = $totalWidth / $numCols;
        return array_fill(0, $numCols, $baseWidth);
    }

    public function isAvailable(): bool
    {
        return $this->tcpdfAvailable;
    }
}

