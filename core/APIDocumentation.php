<?php
declare(strict_types=1);
/**
 * API Documentation Generator for SPRIN
 * Generates OpenAPI 3.0 / Swagger documentation
 */

class APIDocumentation {
    
    private $baseUrl;
    private $version;
    
    public function __construct() {
        require_once __DIR__ . '/config.php';
        $this->baseUrl = BASE_URL . '/api';
        $this->version = API_VERSION ?? 'v1';
    }
    
    /**
     * Generate OpenAPI specification
     */
    public function generateSpec() {
        return [
            'openapi' => '3.0.0',
            'info' => $this->getInfo(),
            'servers' => $this->getServers(),
            'paths' => $this->getPaths(),
            'components' => $this->getComponents(),
            'security' => [
                ['bearerAuth' => []]
            ],
            'tags' => $this->getTags()
        ];
    }
    
    /**
     * API Information
     */
    private function getInfo() {
        return [
            'title' => 'SPRIN API',
            'description' => 'Sistem Personil dan Jadwal Polres Samosir - REST API Documentation',
            'version' => $this->version,
            'contact' => [
                'name' => 'POLRES Samosir - Bagian Operasional',
                'email' => 'bagops@polressamosir.go.id'
            ],
            'license' => [
                'name' => 'Private',
                'url' => 'https://polressamosir.go.id'
            ]
        ];
    }
    
    /**
     * API Servers
     */
    private function getServers() {
        return [
            [
                'url' => $this->baseUrl,
                'description' => 'Local Development Server'
            ],
            [
                'url' => 'https://api.polressamosir.go.id/v1',
                'description' => 'Production Server'
            ]
        ];
    }
    
    /**
     * API Paths (Endpoints)
     */
    private function getPaths() {
        return [
            // Authentication
            '/auth/login' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'User login',
                    'description' => 'Authenticate user with username and password',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['username', 'password'],
                                    'properties' => [
                                        'username' => [
                                            'type' => 'string',
                                            'example' => 'bagops'
                                        ],
                                        'password' => [
                                            'type' => 'string',
                                            'format' => 'password',
                                            'example' => 'admin123'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Login successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/LoginResponse'
                                    ]
                                ]
                            ]
                        ],
                        '401' => [
                            'description' => 'Invalid credentials'
                        ]
                    ]
                ]
            ],
            
            // Personil
            '/personil' => [
                'get' => [
                    'tags' => ['Personil'],
                    'summary' => 'Get personil list',
                    'description' => 'Retrieve list of personil with optional filters',
                    'parameters' => [
                        [
                            'name' => 'unsur',
                            'in' => 'query',
                            'schema' => ['type' => 'string'],
                            'description' => 'Filter by unsur kode'
                        ],
                        [
                            'name' => 'bagian',
                            'in' => 'query',
                            'schema' => ['type' => 'integer'],
                            'description' => 'Filter by bagian ID'
                        ],
                        [
                            'name' => 'search',
                            'in' => 'query',
                            'schema' => ['type' => 'string'],
                            'description' => 'Search by name or NRP'
                        ],
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'schema' => [
                                'type' => 'integer',
                                'default' => 1
                            ],
                            'description' => 'Page number'
                        ],
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'schema' => [
                                'type' => 'integer',
                                'default' => 100
                            ],
                            'description' => 'Items per page'
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Personil list',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/PersonilListResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'post' => [
                    'tags' => ['Personil'],
                    'summary' => 'Create personil',
                    'description' => 'Create new personil record',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/PersonilCreate'
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Personil created successfully'
                        ],
                        '400' => [
                            'description' => 'Invalid input data'
                        ]
                    ]
                ]
            ],
            
            '/personil/{id}' => [
                'get' => [
                    'tags' => ['Personil'],
                    'summary' => 'Get personil by ID',
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                            'description' => 'Personil ID'
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Personil details',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/Personil'
                                    ]
                                ]
                            ]
                        ],
                        '404' => [
                            'description' => 'Personil not found'
                        ]
                    ]
                ],
                'put' => [
                    'tags' => ['Personil'],
                    'summary' => 'Update personil',
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer']
                        ]
                    ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/PersonilUpdate'
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Personil updated'
                        ]
                    ]
                ],
                'delete' => [
                    'tags' => ['Personil'],
                    'summary' => 'Delete personil',
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer']
                        ]
                    ],
                    'responses' => [
                        '204' => [
                            'description' => 'Personil deleted'
                        ]
                    ]
                ]
            ],
            
            // Statistics
            '/stats/unsur' => [
                'get' => [
                    'tags' => ['Statistics'],
                    'summary' => 'Get unsur statistics',
                    'description' => 'Retrieve personil distribution by unsur',
                    'responses' => [
                        '200' => [
                            'description' => 'Unsur statistics',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/UnsurStatsResponse'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            
            // Export
            '/export/personil' => [
                'get' => [
                    'tags' => ['Export'],
                    'summary' => 'Export personil data',
                    'parameters' => [
                        [
                            'name' => 'format',
                            'in' => 'query',
                            'required' => true,
                            'schema' => [
                                'type' => 'string',
                                'enum' => ['xlsx', 'pdf', 'csv']
                            ],
                            'description' => 'Export format'
                        ],
                        [
                            'name' => 'unsur',
                            'in' => 'query',
                            'schema' => ['type' => 'string'],
                            'description' => 'Filter by unsur'
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Export file'
                        ]
                    ]
                ]
            ],
            
            // Search
            '/search' => [
                'get' => [
                    'tags' => ['Search'],
                    'summary' => 'Advanced search',
                    'parameters' => [
                        [
                            'name' => 'q',
                            'in' => 'query',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                            'description' => 'Search query'
                        ],
                        [
                            'name' => 'type',
                            'in' => 'query',
                            'schema' => [
                                'type' => 'string',
                                'enum' => ['personil', 'bagian', 'unsur'],
                                'default' => 'personil'
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Search results'
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * API Components (Schemas, Security)
     */
    private function getComponents() {
        return [
            'schemas' => [
                // Personil Schema
                'Personil' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'nama' => ['type' => 'string'],
                        'nama_lengkap' => ['type' => 'string'],
                        'nrp' => ['type' => 'string'],
                        'gelar_pendidikan' => ['type' => 'string'],
                        'JK' => [
                            'type' => 'string',
                            'enum' => ['L', 'P']
                        ],
                        'status_nikah' => ['type' => 'string'],
                        'status_ket' => ['type' => 'string'],
                        'nama_pangkat' => ['type' => 'string'],
                        'nama_jabatan' => ['type' => 'string'],
                        'nama_bagian' => ['type' => 'string'],
                        'nama_unsur' => ['type' => 'string'],
                        'status_kepegawaian' => ['type' => 'string'],
                        'created_at' => [
                            'type' => 'string',
                            'format' => 'date-time'
                        ]
                    ]
                ],
                
                'PersonilCreate' => [
                    'type' => 'object',
                    'required' => ['nama', 'nrp', 'id_pangkat', 'id_jabatan', 'id_bagian'],
                    'properties' => [
                        'nama' => ['type' => 'string'],
                        'nrp' => ['type' => 'string'],
                        'id_pangkat' => ['type' => 'integer'],
                        'id_jabatan' => ['type' => 'integer'],
                        'id_bagian' => ['type' => 'integer'],
                        'id_unsur' => ['type' => 'integer'],
                        'JK' => [
                            'type' => 'string',
                            'enum' => ['L', 'P']
                        ]
                    ]
                ],
                
                'PersonilUpdate' => [
                    'type' => 'object',
                    'properties' => [
                        'nama' => ['type' => 'string'],
                        'nrp' => ['type' => 'string'],
                        'id_pangkat' => ['type' => 'integer'],
                        'id_jabatan' => ['type' => 'integer'],
                        'id_bagian' => ['type' => 'integer'],
                        'status_ket' => ['type' => 'string']
                    ]
                ],
                
                'PersonilListResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean'],
                        'data' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/Personil'
                            ]
                        ],
                        'pagination' => [
                            'type' => 'object',
                            'properties' => [
                                'total' => ['type' => 'integer'],
                                'page' => ['type' => 'integer'],
                                'limit' => ['type' => 'integer'],
                                'total_pages' => ['type' => 'integer']
                            ]
                        ]
                    ]
                ],
                
                // Login Schema
                'LoginResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean'],
                        'message' => ['type' => 'string'],
                        'data' => [
                            'type' => 'object',
                            'properties' => [
                                'token' => ['type' => 'string'],
                                'user' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'username' => ['type' => 'string'],
                                        'name' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                
                // Statistics Schema
                'UnsurStatsResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => ['type' => 'boolean'],
                        'data' => [
                            'type' => 'object',
                            'properties' => [
                                'unsur' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'kode_unsur' => ['type' => 'string'],
                                            'nama_unsur' => ['type' => 'string'],
                                            'total_personil' => ['type' => 'integer'],
                                            'polri_count' => ['type' => 'integer'],
                                            'asn_count' => ['type' => 'integer'],
                                            'p3k_count' => ['type' => 'integer']
                                        ]
                                    ]
                                ],
                                'total_all' => ['type' => 'integer']
                            ]
                        ]
                    ]
                ],
                
                // Error Schema
                'Error' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                            'example' => false
                        ],
                        'message' => ['type' => 'string'],
                        'errors' => [
                            'type' => 'object',
                            'additionalProperties' => true
                        ]
                    ]
                ]
            ],
            
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'JWT token obtained from /auth/login'
                ]
            ]
        ];
    }
    
    /**
     * API Tags
     */
    private function getTags() {
        return [
            [
                'name' => 'Authentication',
                'description' => 'User login, logout, and token management'
            ],
            [
                'name' => 'Personil',
                'description' => 'Personil CRUD operations and management'
            ],
            [
                'name' => 'Statistics',
                'description' => 'Dashboard statistics and reports'
            ],
            [
                'name' => 'Export',
                'description' => 'Data export functionality (PDF, Excel, CSV)'
            ],
            [
                'name' => 'Search',
                'description' => 'Advanced search with filters'
            ]
        ];
    }
    
    /**
     * Generate and save Swagger JSON
     */
    public function generateDocs($outputPath = null) {
        $outputPath = $outputPath ?? __DIR__ . '/../public/api-docs/swagger.json';
        
        $spec = $this->generateSpec();
        
        // Create directory if not exists
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(
            $outputPath, 
            json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        return [
            'success' => true,
            'path' => $outputPath,
            'url' => str_replace(__DIR__ . '/../', '/', $outputPath)
        ];
    }
    
    /**
     * Get Swagger UI HTML
     */
    public function getSwaggerUI() {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SPRIN API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3/swagger-ui.css" />
    <style>
        body { margin: 0; padding: 0; }
        #swagger-ui { max-width: 1460px; margin: 0 auto; }
        .topbar { display: none; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@3/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@3/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "swagger.json",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                validatorUrl: null
            });
        };
    </script>
</body>
</html>';
    }
    
    /**
     * Generate and save Swagger UI
     */
    public function generateSwaggerUI($outputPath = null) {
        $outputPath = $outputPath ?? __DIR__ . '/../public/api-docs/index.html';
        
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outputPath, $this->getSwaggerUI());
        
        return [
            'success' => true,
            'path' => $outputPath,
            'url' => str_replace(__DIR__ . '/../', '/', $outputPath)
        ];
    }
    
    /**
     * Generate complete documentation
     */
    public function generateAll() {
        return [
            'swagger_json' => $this->generateDocs(),
            'swagger_ui' => $this->generateSwaggerUI(),
            'access_url' => '/api-docs/',
            'api_base' => $this->baseUrl
        ];
    }
}

?>