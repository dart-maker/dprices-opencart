<?php
/**
 * Model Module D.Prices Class
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

class ModelExtensionModuleDPrices extends Model {

    /**
     * Get Information data.
     * 
     * @param string $type
     * @param string $code
     * 
     * @return array $query->row
     */
    public function getExtensionByCode(string $type, string $code): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");

        return $query->row;
    }
}