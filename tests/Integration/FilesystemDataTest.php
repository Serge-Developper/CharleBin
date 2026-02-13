<?php

namespace PrivateBin\Test\Integration;

use PHPUnit\Framework\TestCase;
use PrivateBin\Data\Filesystem;

/**
 * Tests d'intégration pour Data/Filesystem (équivalent Repository)
 */
class FilesystemDataTest extends TestCase
{
    private $storage;
    private $testDir;

    /**
     * Configuration avant chaque test
     */
    protected function setUp(): void
    {
        // Créer un répertoire temporaire pour les tests
        $this->testDir = sys_get_temp_dir() . '/privatebin_test_' . uniqid();
        mkdir($this->testDir, 0700, true);
        
        // Initialiser le storage
        $this->storage = new Filesystem(['dir' => $this->testDir]);
    }

    /**
     * Nettoyage après chaque test
     */
    protected function tearDown(): void
    {
        // Supprimer le répertoire de test
        if (is_dir($this->testDir)) {
            $this->deleteDirectory($this->testDir);
        }
    }

    /**
     * Helper pour supprimer un répertoire récursivement
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Test 5: Créer et lire un paste
     */
    public function testCreateAndReadPaste()
    {
        $pasteId = 'testpaste123';
        $pasteData = [
            'data' => 'encrypted_data_here',
            'meta' => [
                'expire' => '1week',
                'created' => time(),
            ],
        ];

        // Créer le paste
        $created = $this->storage->create($pasteId, $pasteData);
        $this->assertTrue($created, "Le paste devrait être créé avec succès");

        // Lire le paste
        $retrieved = $this->storage->read($pasteId);
        $this->assertNotFalse($retrieved, "Le paste devrait être récupérable");
        $this->assertEquals($pasteData['data'], $retrieved['data']);
    }

    /**
     * Test 6: Supprimer un paste existant
     */
    public function testDeleteExistingPaste()
    {
        $pasteId = 'testpaste456';
        $pasteData = [
            'data' => 'encrypted_data_to_delete',
            'meta' => ['created' => time()],
        ];

        // Créer puis supprimer
        $this->storage->create($pasteId, $pasteData);
        $this->storage->delete($pasteId);

        // Vérifier que le paste n'existe plus
        $retrieved = $this->storage->read($pasteId);
        $this->assertFalse($retrieved, "Le paste supprimé ne devrait plus exister");
    }

    /**
     * Test 7: Vérifier l'existence d'un paste
     */
    public function testPasteExists()
    {
        $pasteId = 'testpaste789';
        $pasteData = [
            'data' => 'test_existence',
            'meta' => ['created' => time()],
        ];

        // Le paste n'existe pas encore
        $existsBefore = $this->storage->exists($pasteId);
        $this->assertFalse($existsBefore, "Le paste ne devrait pas exister avant création");

        // Créer le paste
        $this->storage->create($pasteId, $pasteData);

        // Le paste existe maintenant
        $existsAfter = $this->storage->exists($pasteId);
        $this->assertTrue($existsAfter, "Le paste devrait exister après création");
    }

    /**
     * Test 8: Lire un paste inexistant retourne false
     */
    public function testReadNonExistentPasteReturnsFalse()
    {
        $nonExistentId = 'thisdoesnotexist999';
        
        $result = $this->storage->read($nonExistentId);
        
        $this->assertFalse($result, "La lecture d'un paste inexistant devrait retourner false");
    }

    /**
     * Test bonus: Créer un paste avec des métadonnées complexes
     */
    public function testCreatePasteWithComplexMetadata()
    {
        $pasteId = 'complex_meta_paste';
        $pasteData = [
            'data' => 'encrypted_complex_data',
            'meta' => [
                'expire' => '1month',
                'created' => time(),
                'formatter' => 'markdown',
                'opendiscussion' => true,
                'burnafterreading' => false,
            ],
        ];

        $created = $this->storage->create($pasteId, $pasteData);
        $this->assertTrue($created);

        $retrieved = $this->storage->read($pasteId);
        $this->assertNotFalse($retrieved);
        $this->assertEquals($pasteData['meta']['formatter'], $retrieved['meta']['formatter']);
        $this->assertTrue($retrieved['meta']['opendiscussion']);
        $this->assertFalse($retrieved['meta']['burnafterreading']);
    }
}
