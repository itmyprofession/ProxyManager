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
 * and is licensed under the MIT license.
 */

namespace ProxyManager\Factory;

use ProxyManager\Configuration;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManager\Generator\ClassGenerator;
use ReflectionClass;
use Closure;

/**
 * Factory responsible of producing ghost objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingGhostFactory
{
    /**
     * @var \ProxyManager\Configuration
     */
    protected $configuration;

    /**
     * @var bool
     */
    protected $autoGenerate;

    /**
     * @var \ProxyManager\Inflector\ClassNameInflectorInterface
     */
    protected $inflector;

    /**
     * Cached generated class names
     *
     * @var string[]
     */
    protected $generatedClasses = array();

    /**
     * @param \ProxyManager\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;

        // localizing some properties for performance
        $this->autoGenerate  = $this->configuration->doesAutoGenerateProxies();
        $this->inflector     = $this->configuration->getClassNameInflector();
    }

    /**
     * @param string   $className   name of the class to be proxied
     * @param \Closure $initializer initializer to be passed to the proxy
     *
     * @return \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\GhostObjectInterface
     */
    public function createProxy($className, Closure $initializer)
    {
        if (! isset($this->generatedClasses[$className])) {
            $this->generatedClasses[$className] = $this->inflector->getProxyClassName(
                $className,
                array('factory' => get_class($this))
            );
        }

        $proxyClassName = $this->generatedClasses[$className];

        if ($this->autoGenerate && ! class_exists($proxyClassName)) {
            $className = $this->inflector->getUserClassName($className);
            $phpClass  = new ClassGenerator($proxyClassName);
            $generator = new LazyLoadingGhostGenerator();

            $generator->generate(new ReflectionClass($className), $phpClass);
            $this->configuration->getGeneratorStrategy()->generate($phpClass);
            $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
        }

        return new $proxyClassName($initializer);
    }
}
