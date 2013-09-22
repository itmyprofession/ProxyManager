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

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\Constructor;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\Constructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructure()
    {
        $adapter = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $adapter->expects($this->any())->method('getName')->will($this->returnValue('adapter'));
        
        $classname = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $classname->expects($this->any())->method('getName')->will($this->returnValue('classname'));
        
        $reflection  = new ReflectionClass('ProxyManagerTestAsset\\ClassWithMixedProperties');
        $constructor = new Constructor($reflection, $classname, $adapter);

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(2, $constructor->getParameters());
        $this->assertSame(
            "\$this->classname = \"".$reflection->getName()."\";\n\$this->adapter = \$adapter;",
            $constructor->getBody()
        );
    }
}