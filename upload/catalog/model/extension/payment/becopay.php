<?php
/**
 * 2018 Becopay
 */

class ModelExtensionPaymentBecopay extends Model
{
    /**
     * Becopay module code
     */
    const BECOPAY_CODE = 'becopay';

    /**
     * Becopay module status config
     */
    const CONFIG_BECOPAY_ENABLE = 'payment_becopay_status';

    /**
     * Becopay title config
     */
    const CONFIG_BECOPAY_TITLE = 'payment_becopay_title';

    /**
     * Becopay geo config
     */
    const CONFIG_BECOPAY_GEO = 'payment_becopay_geo_zone_id';

    /**
     * Becopay total config
     */
    const CONFIG_BECOPAY_TOTAL = 'payment_becopay_total';

    /**
     * Becopay payment sort config
     */
    const CONFIG_BECOPAY_SORT = 'payment_becopay_sort_order';

    /**
     * Empty value
     */
    const EMPTY_VAL = '';

    /**
     * Zero value
     */
    const ZERO_VAL = 0;

    /**
     * @param object $address
     * @param double $total
     *
     * @return array
     */
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/becopay');

        if ($this->config->get($this::CONFIG_BECOPAY_ENABLE)) {
            $geoZoneID = (int)$this->config->get($this::CONFIG_BECOPAY_GEO);
            $countryID = (int)$address['country_id'];

            $zoneID    = (int)$address['zone_id'];
            $zones     = implode(',', array($zoneID, $this::ZERO_VAL));

            $query = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone"
                . " WHERE geo_zone_id = '" . $geoZoneID
                . "' AND country_id = '" . $countryID
                . "' AND zone_id IN (" . $zones . ")"
            );

            if (
                $this->config->get($this::CONFIG_BECOPAY_TOTAL) > 0
                && $this->config->get($this::CONFIG_BECOPAY_TOTAL) > $total
            ) {
                $status = false;
            } elseif (
            !$this->config->get($this::CONFIG_BECOPAY_GEO)
            ) {
                $status = true;
            } elseif ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        $method_data = array();

        $title = $this->config->get($this::CONFIG_BECOPAY_TITLE);
        if (empty($title)) {
            $title = $this->language->get('text_title');
        }

        $sort = $this->config->get($this::CONFIG_BECOPAY_SORT);

        if ($status) {
            $method_data = array(
                'code'       => 'becopay',
                'title'      => $title,
                'terms'      => $this::EMPTY_VAL,
                'sort_order' => $sort
            );
        }

        return $method_data;
    }
}