<?php
//******************************************************************************
//* Reporting configuration
//******************************************************************************
return [
    /** Our choice */
    'default'     => 'kibana',
    /** Our connections */
    'connections' => [
        'kibana' => [
            'base-uri' => '//kibana.enterprise.dreamfactory.com',
            'reports'  => [
                'api-usage' => [
                    'title'      => 'API Usage',
                    'index-type' => 'cluster-east-2',
                    'query'      => '/#/visualize/edit/{search_type}?embed&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27_type:{index_type}{search_string}%27%29%29,vis:%28aggs:!%28%28id:%272%27,params:%28%29,schema:metric,type:count%29,%28id:%274%27,params:%28field:{search_field},order:desc,orderBy:%272%27,size:15%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,index:%27logstash-*%27,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29&{time_period}',
                ],
            ],
        ],
    ],
];
