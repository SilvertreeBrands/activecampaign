<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model;

class ResourceIterator extends \Magento\Framework\Model\ResourceModel\Iterator
{
    /**
     * @var int
     */
    public $processedCount = 0;

    /**
     * @var int
     */
    public $errorCount = 0;

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function walk($query, array $callbacks, array $args = [], $connection = null)
    {
        $stmt = $this->_getStatement($query, $connection);
        $args['idx'] = 0;

        while ($row = $stmt->fetch()) {
            $args['row'] = $row;

            foreach ($callbacks as $callback) {
                try {
                    $result = call_user_func($callback, $args);

                    if (!empty($result)) {
                        $args = array_merge($args, (array)$result);
                    }
                } catch (\Exception $e) {
                    $this->errorCount++;
                }

                $this->processedCount++;
            }

            $args['idx']++;
        }

        return $this;
    }
}
