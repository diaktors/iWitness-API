<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
*/

namespace Doctrine\DBAL\Migrations\Configuration;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\OutputWriter;

/**
 * Load migration configuration information from a XML configuration file.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class PhpConfiguration extends AbstractFileConfiguration
{

    /**
     * @var array
     */
    private $_config = null;


    public function __construct(array $config, Connection $connection, OutputWriter $outputWriter = null)
    {
        $this->_config = $config;

        parent::__construct($connection, $outputWriter);
    }

    /**
     * @inheritdoc
     */
    protected function doLoad($file)
    {

        $config = $this->_config;

        if (isset($config['name'])) {
            $this->setName((string)$config['name']);
        }
        if (isset($config['table'])) {
            $this->setMigrationsTableName((string)$config['table']);
        }
        if (isset($config['migrations_namespace'])) {
            $this->setMigrationsNamespace((string)$config['migrations_namespace']);
        }


        if (isset($config['migrations_directory'])) {

            $releaseDirectory = $this->getDirectoryRelativeToFile(
                $file, (string)$config['migrations_directory'],
                $config['release']
            );


            //create directory if not exist
            if (!file_exists($releaseDirectory)) {
                mkdir($releaseDirectory, 0777, true);
            }

            $relPath = realpath($releaseDirectory);

            if (($relPath == false)) {
                throw new \Exception('Directory "' . $releaseDirectory . '" does not exist!');
            }
            $this->setMigrationsDirectory($relPath);
            $this->registerMigrationsFromDirectory($relPath, $config['release']);
        }
    }
}
