<?php

/**
 * Apache Echart options
 */
final class echartOptions
{
    private ArrayObject $echart;

    public function __construct(array $xData = [], array $series = [], $useDefaults = true)
    {
        $this->echart = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        if ($useDefaults === true) {
            $this->setDefaults($xData, $series);
        }
    }

    /**
     * Set default options
     * 
     * Line charts are common. This sets default options
     * to produce a line chart for a typical uzLET.
     *
     * @param array $xData
     * @param array $series
     * @return void
     */
    private function setDefaults(array $xData = [], array $series = []): void
    {
        $this->echart->yAxis['type'] = 'value';
        $this->echart->xAxis = [
            'data' => $xData,
            'type' => 'category',
            'axisLabel' => ['rotate' => 45]
        ];

        foreach ($series as $values) {
            if (is_array($values)) {
                $this->echart->series[] = $values;
            } else {
                $this->echart->series = [
                    'data' => $series,
                    'symbolSize' => 10,
                    'type' => 'line'
                ];
            }
        }

        $this->echart->tooltip = [new stdClass()];
        $this->echart->grid = [
            'left' => '15%',
            'top' => '10%',
            'right' => '10%',
            'bottom' => '70px'
        ];
    }

    /**
     * Set Apache Echart option
     *
     * @param string $option
     * @param mixed $value
     * @return void
     */
    public function setOption(string $option, mixed $value): void
    {
        $this->echart->$option = $value;
    }

    /**
     * Return Apache Echart options Array
     *
     * @return ArrayObject
     */
    public function getOptionsArray(): ArrayObject
    {
        return $this->echart;
    }

    /**
     * Return Apache Echart options JSON string
     *
     * @param bool $pretty pretty print JSON
     * @return string
     */
    public function getOptionsJSON(bool $pretty = \false): string
    {
        if ($pretty) {
            return json_encode($this->getOptionsArray(), JSON_PRETTY_PRINT);
        }
        return json_encode($this->getOptionsArray());
    }
}
