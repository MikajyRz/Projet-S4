<?php
require_once __DIR__ . '/../models/Pret.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Remboursement.php';

class PdfController {

    public static function generatePretPdf($id_pret) {
        try {
            // Récupérer les données du prêt
            $pret = Pret::getById($id_pret);
            if (!$pret) {
                Flight::halt(404, json_encode(['error' => 'Prêt non trouvé']));
            }

            // Récupérer les remboursements
            $remboursements = Remboursement::getByPretId($id_pret);

            // Générer le HTML du PDF
            $html = self::generatePretHtml($pret, $remboursements);

            // Retourner le HTML (pour l'instant, on peut utiliser une bibliothèque comme TCPDF ou DOMPDF)
            Flight::json([
                'success' => true,
                'html' => $html,
                'pret' => $pret,
                'remboursements' => $remboursements
            ]);

        } catch (Exception $e) {
            Flight::halt(500, json_encode(['error' => $e->getMessage()]));
        }
    }

    public static function generatePretFpdf($id_pret) {
        require_once __DIR__ . '/../../lib/fpdf/fpdf.php';
        require_once __DIR__ . '/../models/Pret.php';
        require_once __DIR__ . '/../models/Client.php';
        require_once __DIR__ . '/../models/Remboursement.php';

        $pret = Pret::getById($id_pret);
        if (!$pret) {
            Flight::halt(404, 'Prêt non trouvé');
        }
        $remboursements = Remboursement::getByPretId($id_pret);

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('Contrat de Prêt N° ' . $pret['id_pret']), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, utf8_decode('Date d\'émission : ' . date('d/m/Y')), 0, 1, 'C');
        $pdf->Ln(5);

        // Infos prêt
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Informations du Prêt'), 0, 1);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 8, utf8_decode('Numéro de Prêt :'), 0, 0);
        $pdf->Cell(60, 8, $pret['id_pret'], 0, 1);
        $pdf->Cell(60, 8, utf8_decode('Date du Prêt :'), 0, 0);
        $pdf->Cell(60, 8, date('d/m/Y', strtotime($pret['date_pret'])), 0, 1);
        $pdf->Cell(60, 8, utf8_decode('Montant Prêté :'), 0, 0);
        $pdf->Cell(60, 8, number_format($pret['montant'], 2, ',', ' ') . ' €', 0, 1);
        $pdf->Cell(60, 8, utf8_decode('Statut :'), 0, 0);
        $pdf->Cell(60, 8, ucfirst($pret['statuts']), 0, 1);
        $pdf->Ln(2);

        // Infos client
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Informations du Client'), 0, 1);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 8, utf8_decode('Nom du Client :'), 0, 0);
        $pdf->Cell(60, 8, utf8_decode($pret['client_nom']), 0, 1);
        $pdf->Cell(60, 8, utf8_decode('Type de Prêt :'), 0, 0);
        $pdf->Cell(60, 8, utf8_decode($pret['type_pret_nom']), 0, 1);
        $pdf->Cell(60, 8, utf8_decode('Taux d\'Intérêt :'), 0, 0);
        $pdf->Cell(60, 8, $pret['taux'] . '%', 0, 1);
        $pdf->Cell(60, 8, utf8_decode('Taux d\'Assurance :'), 0, 0);
        $pdf->Cell(60, 8, ($pret['assurance'] ? $pret['assurance'] . '%' : '0%'), 0, 1);
        $pdf->Ln(2);

        // Échéancier
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Échéancier de Remboursement'), 0, 1);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 8, utf8_decode('Échéance'), 1);
        $pdf->Cell(25, 8, 'Capital', 1);
        $pdf->Cell(25, 8, utf8_decode('Intérêts'), 1);
        $pdf->Cell(25, 8, 'Assurance', 1);
        $pdf->Cell(30, 8, 'Total', 1);
        $pdf->Cell(35, 8, 'Capital Restant', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        foreach ($remboursements as $remb) {
            $pdf->Cell(25, 8, $remb['mois'] . '/' . $remb['annee'], 1);
            $pdf->Cell(25, 8, number_format($remb['capital'], 2, ',', ' '), 1);
            $pdf->Cell(25, 8, number_format($remb['interet'], 2, ',', ' '), 1);
            $pdf->Cell(25, 8, number_format($remb['assurance'], 2, ',', ' '), 1);
            $pdf->Cell(30, 8, number_format($remb['montant_total'], 2, ',', ' '), 1);
            $pdf->Cell(35, 8, number_format($remb['capital_restant'], 2, ',', ' '), 1);
            $pdf->Ln();
        }
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, utf8_decode('Signature du Prêteur : ___________________________'), 0, 1);
        $pdf->Cell(0, 8, utf8_decode('Signature de l\'Emprunteur : _____________________'), 0, 1);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="pret_' . $pret['id_pret'] . '.pdf"');
        $pdf->Output('I', 'pret_' . $pret['id_pret'] . '.pdf');
        exit;
    }

    private static function generatePretHtml($pret, $remboursements) {
        $date = date('d/m/Y');
        $totalRemboursement = array_sum(array_column($remboursements, 'montant_total'));
        $totalInterets = array_sum(array_column($remboursements, 'interet'));
        $totalAssurance = array_sum(array_column($remboursements, 'assurance'));

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Contrat de Prêt #{$pret['id_pret']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { color: #2c3e50; margin: 0; }
                .header p { color: #7f8c8d; margin: 5px 0; }
                .section { margin-bottom: 25px; }
                .section h2 { color: #34495e; border-bottom: 1px solid #bdc3c7; padding-bottom: 5px; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 15px 0; }
                .info-item { background: #f8f9fa; padding: 15px; border-radius: 8px; }
                .info-label { font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
                .info-value { color: #34495e; }
                .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .table th, .table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                .table th { background: #34495e; color: white; }
                .table tr:nth-child(even) { background: #f8f9fa; }
                .total-row { background: #2c3e50 !important; color: white; font-weight: bold; }
                .signature-section { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
                .signature-box { border: 1px solid #ddd; padding: 20px; text-align: center; }
                .signature-line { border-top: 1px solid #333; margin-top: 50px; }
                .footer { margin-top: 40px; text-align: center; color: #7f8c8d; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🏦 SYSTÈME DE PRÊTS</h1>
                <p>Contrat de Prêt N° {$pret['id_pret']}</p>
                <p>Date d'émission : {$date}</p>
            </div>

            <div class='section'>
                <h2>📋 Informations du Prêt</h2>
                <div class='info-grid'>
                    <div class='info-item'>
                        <div class='info-label'>Numéro de Prêt</div>
                        <div class='info-value'>#{$pret['id_pret']}</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Date du Prêt</div>
                        <div class='info-value'>" . date('d/m/Y', strtotime($pret['date_pret'])) . "</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Montant Prêté</div>
                        <div class='info-value'>" . number_format($pret['montant'], 2, ',', ' ') . " €</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Statut</div>
                        <div class='info-value'>" . ucfirst($pret['statuts']) . "</div>
                    </div>
                </div>
            </div>

            <div class='section'>
                <h2>👤 Informations du Client</h2>
                <div class='info-grid'>
                    <div class='info-item'>
                        <div class='info-label'>Nom du Client</div>
                        <div class='info-value'>{$pret['client_nom']}</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Type de Prêt</div>
                        <div class='info-value'>{$pret['type_pret_nom']}</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Taux d'Intérêt</div>
                        <div class='info-value'>{$pret['taux']}%</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Taux d'Assurance</div>
                        <div class='info-value'>" . ($pret['assurance'] ? $pret['assurance'] . '%' : '0%') . "</div>
                    </div>
                </div>
            </div>

            <div class='section'>
                <h2>📊 Échéancier de Remboursement</h2>
                <table class='table'>
                    <thead>
                        <tr>
                            <th>Échéance</th>
                            <th>Capital</th>
                            <th>Intérêts</th>
                            <th>Assurance</th>
                            <th>Total</th>
                            <th>Capital Restant</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($remboursements as $remb) {
            $html .= "
                        <tr>
                            <td>{$remb['mois']}/{$remb['annee']}</td>
                            <td>" . number_format($remb['capital'], 2, ',', ' ') . " €</td>
                            <td>" . number_format($remb['interet'], 2, ',', ' ') . " €</td>
                            <td>" . number_format($remb['assurance'], 2, ',', ' ') . " €</td>
                            <td>" . number_format($remb['montant_total'], 2, ',', ' ') . " €</td>
                            <td>" . number_format($remb['capital_restant'], 2, ',', ' ') . " €</td>
                        </tr>";
        }

        $html .= "
                        <tr class='total-row'>
                            <td><strong>TOTAL</strong></td>
                            <td><strong>" . number_format($pret['montant'], 2, ',', ' ') . " €</strong></td>
                            <td><strong>" . number_format($totalInterets, 2, ',', ' ') . " €</strong></td>
                            <td><strong>" . number_format($totalAssurance, 2, ',', ' ') . " €</strong></td>
                            <td><strong>" . number_format($totalRemboursement, 2, ',', ' ') . " €</strong></td>
                            <td><strong>0,00 €</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class='section'>
                <h2>📝 Conditions du Prêt</h2>
                <div class='info-grid'>
                    <div class='info-item'>
                        <div class='info-label'>Durée du Prêt</div>
                        <div class='info-value'>" . count($remboursements) . " mois</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Mensualité Moyenne</div>
                        <div class='info-value'>" . number_format($totalRemboursement / count($remboursements), 2, ',', ' ') . " €</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Coût Total du Crédit</div>
                        <div class='info-value'>" . number_format($totalInterets + $totalAssurance, 2, ',', ' ') . " €</div>
                    </div>
                    <div class='info-item'>
                        <div class='info-label'>Taux Effectif Global</div>
                        <div class='info-value'>" . number_format((($totalInterets + $totalAssurance) / $pret['montant']) * 100, 2, ',', ' ') . "%</div>
                    </div>
                </div>
            </div>

            <div class='signature-section'>
                <div class='signature-box'>
                    <p><strong>Signature du Prêteur</strong></p>
                    <div class='signature-line'></div>
                    <p style='margin-top: 10px;'>Date : {$date}</p>
                </div>
                <div class='signature-box'>
                    <p><strong>Signature de l'Emprunteur</strong></p>
                    <div class='signature-line'></div>
                    <p style='margin-top: 10px;'>Date : {$date}</p>
                </div>
            </div>

            <div class='footer'>
                <p>Ce document constitue le contrat de prêt officiel entre les parties.</p>
                <p>Pour toute question, veuillez contacter notre service client.</p>
            </div>
        </body>
        </html>";
    }
} 