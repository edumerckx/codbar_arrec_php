<?php

/**
 *
 * @author eduardo
 * @copyright CC
 *
 */
class Arrecadacao {

	/**
	 * Código de barras para arrecadação
	 * com 44 posições
	 *
	 */
	private $codigo_barras;
	
	/**
	 * Produto - sempre 8 para arrecadação
	 * 
	 */
	private $produto;
	
	/**
	 * Segmento
	 * 
	 */
	private $segmento;
	
	/**
	 * Indicador de valor - efetivo ou referência
	 * 
	 */
	private $indicador_valor;
	
	/**
	 * Dígito verificador do código de barras
	 * 
	 */
	private $dv;
	
	/**
	 * Valor
	 * 
	 */
	private $valor;

	/**
	 * Identificação empresa
	 *  
	 */
	private $id_empresa;
	
	/**
	 * Campo livre - de uso da empresa/orgão
	 * 
	 */
	private $campo_livre;
	
	/**
	 * 
	 * @param $codigo_barras
	 */
	public function __construct($codigo_barras = '') {

		if ($codigo_barras != '') {
			self::setCodigoBarras($codigo_barras);
		}
	}

	/**
	 * Valida o tamanho da string e tipo
	 * (carateres numéricos).
	 * Atribui $codigo_barras
	 *
	 * @param $codigo_barras
	 */
	public function setCodigoBarras($codigo_barras) {

		if (is_numeric($codigo_barras) && strlen($codigo_barras) == 44) {
			$this->codigo_barras = $codigo_barras;
		}
	}
	
	/**
	 * Efetua split nos campos do código de barras
	 * 
	 */
	private function split() {
		
	}
	

	/**
	 * Verifica se o código de barras informado 
	 * é válido
	 * Retorna booleano
	 * 
	 */
	public function codigoBarrasValido() {
		
		try {
			if (!isset($this->codigo_barras)) {
				throw new Exception("Código de barras não informado!");
			}
			
			if (substr($this->codigo_barras, 0, 1) != 8) {
				throw new Exception("Código de barras inválido para arrecadação!");
			}

			
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	/**
	 * Retorna array onde indice/valor correspondem
	 * à descrição do campo/valor.
	 *
	 */
	public function getDadosCodigoBarras() {

		try {
			if (!isset($this->codigo_barras)) {
				throw new Exception("Código de barras não informado!");
			}

			if (substr($this->codigo_barras, 0, 1) != 8) {
				throw new Exception("Código de barras inválido para arrecadação!");
			}

			$retorno = array(); 
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

}