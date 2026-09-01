<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Request\Attribute;

/**
 *
 * @link https://line.net.ar/documentacion/#anexo-1
 */
interface CardCodeInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const AMEX = 'AMEX';
    public const MASTERCARD = 'MASTER';
    public const VISA = 'VISA';
    /**#@-*/

    /**
     * Used to match internal Code against the Gateway Code
     *
     * @see Line_Payment::etc/payment.xml
     *
     * @var array
     */
    public const INTERNAL_CODE_MATCHING_LIST = [
        'AMEX' => self::AMEX,
        'MASTER' => self::MASTERCARD,
        'VISA' => self::VISA
    ];

    /** @var array */
    public const CODE_LIST = [
        ["label" => "American Express", "value" => self::AMEX],
        ["label" => "American Macro", "value" => "AMEXMACRO"],
        ["label" => "American Patagonia", "value" => "AMEXPATAGO"],
        ["label" => "American Santander", "value" => "AMEXRIO"],
        ["label" => "AMEX Santander Selecta", "value" => "AMEXRIOSEL"],
        ["label" => "Cabal", "value" => "CABAL"],
        ["label" => "Cabal Débito", "value" => "DEBITOCABAL"],
        ["label" => "Cabal Municipal Rosario", "value" => "CABAMUNRO"],
        ["label" => "Club Arnet", "value" => "CLUBARNET"],
        ["label" => "Club La Nacion", "value" => "CLUBNACION"],
        ["label" => "Club Personal", "value" => "CLUBPERSON"],
        ["label" => "Diners", "value" => "DINERS"],
        ["label" => "Electrón", "value" => "ELECTRON"],
        ["label" => "Electrón Banco Francés", "value" => "ELECBBVA"],
        ["label" => "Electrón Citibank", "value" => "ELECTCITI"],
        ["label" => "Electrón Hipotecario", "value" => "ELECTBHIP"],
        ["label" => "Electrón Macro", "value" => "ELECMACRO"],
        ["label" => "Electrón Patagonia WEB", "value" => "ELECTPATAGOW"],
        ["label" => "Electrón Patagonia", "value" => "ELECTPATAGO"],
        ["label" => "Electrón Santander Selecta", "value" => "ELECTRIOSE"],
        ["label" => "Electrón Santander WEB", "value" => "ELECTRIOWB"],
        ["label" => "Electrón Santander", "value" => "ELECTRIO"],
        ["label" => "Italcred", "value" => "ITALCRED"],
        ["label" => "Kadicard", "value" => "KADICARD"],
        ["label" => "La Capital", "value" => "LACAPITAL"],
        ["label" => "Maestro", "value" => "MAESTRO"],
        ["label" => "Marcos Juárez", "value" => "MJUAREZ"],
        ["label" => "MASTER Banco Santa Fe", "value" => "MASTERSTAFE"],
        ["label" => "MASTER Patagonia", "value" => "MASTERPATAGO"],
        ["label" => "Mastercard", "value" => self::MASTERCARD],
        ["label" => "Mastercard Banco Cordoba", "value" => "MASTERCBA"],
        ["label" => "Mastercard Banco Córdoba Débito", "value" => "MASTERCBADEBIT"],
        ["label" => "Mastercard Banco Francés", "value" => "MASTERBBVA"],
        ["label" => "Mastercard Banco Santander", "value" => "MASTERRIO"],
        ["label" => "Mastercard Ciudad", "value" => "MASTERCDAD"],
        ["label" => "Mastercard Comafi", "value" => "MASTERCOMAFI"],
        ["label" => "Mastercard Debito", "value" => "MASTERDEBIT"],
        ["label" => "Mastercard Debito Nación", "value" => "MASTERDEBITNAC"],
        ["label" => "Mastercard Dinosaurio", "value" => "MASTERDINO"],
        ["label" => "Mastercard ICBC", "value" => "MASTERICBC"],
        ["label" => "Mastercard Macro", "value" => "MASTERMACR"],
        ["label" => "Mastercard Nación", "value" => "MASTERNAC"],
        ["label" => "Mastercard Nativa", "value" => "MASTERNATI"],
        ["label" => "Nativa", "value" => "NATIVA"],
        ["label" => "Nevada", "value" => "NEVADA"],
        ["label" => "Plan Platino", "value" => "PLAPLATINO"],
        ["label" => "Tarjeta Naranja", "value" => "NARANJA"],
        ["label" => "Ultra", "value" => "ULTRA"],
        ["label" => "Visa", "value" => self::VISA],
        ["label" => "Visa Banco Cordoba", "value" => "VISACBA"],
        ["label" => "Visa Banco Francés", "value" => "VISABBVA"],
        ["label" => "Visa Banco Santa Fe", "value" => "VISASTAFE"],
        ["label" => "Visa Banco Santa Fe", "value" => "VISASTAFE"],
        ["label" => "Visa Citibank", "value" => "VISACITI"],
        ["label" => "Visa Ciudad", "value" => "VISACIUDAD"],
        ["label" => "Visa COMAFI", "value" => "VISACOMAFI"],
        ["label" => "Visa Hipotecario", "value" => "VISABHIP"],
        ["label" => "Visa ICBC", "value" => "VISAICBC"],
        ["label" => "Visa Macro Selecta", "value" => "VISAMASELE"],
        ["label" => "Visa Macro", "value" => "VISAMACRO"],
        ["label" => "Visa Municipal Rosario", "value" => "VISAMUNRO"],
        ["label" => "Visa Nación", "value" => "VISANACION"],
        ["label" => "Visa Patagonia", "value" => "VISAPATAGO"],
        ["label" => "Visa Santander Selecta", "value" => "VISARIOSEL"],
        ["label" => "Visa Santander", "value" => "VISARIO"],
        ["label" => "Visa (elecweb)", "value" => "ELECWEB"],
        ["label" => "VYCARD", "value" => "VYCARD"]
    ];
}
