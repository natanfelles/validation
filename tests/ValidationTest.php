<?php
/*
 * This file is part of The Framework Validation Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Validation;

use Framework\Language\Language;
use Framework\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    protected ValidationMock $validation;

    public function setup() : void
    {
        $this->validation = new ValidationMock();
    }

    public function testParseRule() : void
    {
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => [],
            ],
            $this->validation->parseRule('foo')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => ['bar:baz'],
            ],
            $this->validation->parseRule('foo:bar:baz')
        );
        self::assertSame(
            [
                'rule' => 'fo,o',
                'params' => ['bar:baz'],
            ],
            $this->validation->parseRule('fo,o:bar:baz')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => ['param'],
            ],
            $this->validation->parseRule('foo:param')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => ['param', 'param2'],
            ],
            $this->validation->parseRule('foo:param,param2')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => ['  param', ' param2 '],
            ],
            $this->validation->parseRule('foo:  param, param2 ')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => ['param', 'param2', 'param3'],
            ],
            $this->validation->parseRule('foo:param,param2,param3')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => ['param', 'param2,param3'],
            ],
            $this->validation->parseRule('foo:param,param2\,param3')
        );
        self::assertSame(
            [
                'rule' => 'foo',
                'params' => ['param', 'param2\,param3'],
            ],
            $this->validation->parseRule('foo:param,param2\\\,param3')
        );
    }

    public function testExtractRules() : void
    {
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'params' => [],
                ],
            ],
            $this->validation->extractRules('foo')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'params' => [],
                ],
                [
                    'rule' => 'bar',
                    'params' => [],
                ],
            ],
            $this->validation->extractRules('foo|bar')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'params' => [],
                ],
                [
                    'rule' => 'bar|baz',
                    'params' => [],
                ],
            ],
            $this->validation->extractRules('foo|bar\|baz')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'params' => [],
                ],
                [
                    'rule' => 'bar\|baz',
                    'params' => [],
                ],
            ],
            $this->validation->extractRules('foo|bar\\\|baz')
        );
        self::assertSame(
            [
                [
                    'rule' => 'foo',
                    'params' => ['a', 'b,c'],
                ],
                [
                    'rule' => 'bar',
                    'params' => ['|\|'],
                ],
                [
                    'rule' => 'baz',
                    'params' => [],
                ],
            ],
            $this->validation->extractRules('foo:a,b\,c|bar:\|\\\||baz')
        );
    }

    public function testRule() : void
    {
        self::assertEmpty($this->validation->getRules());
        $this->validation->setRule('foo', 'foo:a|bar');
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'foo',
                    'params' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'params' => [],
                ],
            ],
        ], $this->validation->getRules());
        $this->validation->setRule('bar', ['foo:a', 'bar']);
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'foo',
                    'params' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'params' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'params' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'params' => [],
                ],
            ],
        ], $this->validation->getRules());
        $this->validation->setRule('foo', 'baz');
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'baz',
                    'params' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'params' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'params' => [],
                ],
            ],
        ], $this->validation->getRules());
        $this->validation->setRule('baz', ['b|a|\z:s', 'x']);
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'baz',
                    'params' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'params' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'params' => [],
                ],
            ],
            'baz' => [
                [
                    'rule' => 'b|a|\z',
                    'params' => ['s'],
                ],
                [
                    'rule' => 'x',
                    'params' => [],
                ],
            ],
        ], $this->validation->getRules());
    }

    public function testRules() : void
    {
        self::assertEmpty($this->validation->getRules());
        $this->validation->setRules([
            'foo' => 'baz',
            'bar' => 'foo:a|bar',
            'baz' => ['b|a|\z:s', 'x'],
        ]);
        self::assertSame([
            'foo' => [
                [
                    'rule' => 'baz',
                    'params' => [],
                ],
            ],
            'bar' => [
                [
                    'rule' => 'foo',
                    'params' => ['a'],
                ],
                [
                    'rule' => 'bar',
                    'params' => [],
                ],
            ],
            'baz' => [
                [
                    'rule' => 'b|a|\z',
                    'params' => ['s'],
                ],
                [
                    'rule' => 'x',
                    'params' => [],
                ],
            ],
        ], $this->validation->getRules());
    }

    public function testLabel() : void
    {
        self::assertSame([], $this->validation->getLabels());
        self::assertNull($this->validation->getLabel('foo'));
        $this->validation->setLabel('foo', 'Foo');
        self::assertSame('Foo', $this->validation->getLabel('foo'));
        $this->validation->setLabels(['foo' => 'Foo ', 'bar' => 'Bar']);
        self::assertSame(['foo' => 'Foo ', 'bar' => 'Bar'], $this->validation->getLabels());
        $this->validation->reset();
        self::assertSame([], $this->validation->getLabels());
    }

    public function testSetError() : void
    {
        self::assertSame([], $this->validation->getErrors());
        self::assertNull($this->validation->getError('foo'));
        $this->validation->setError('foo', 'test', ['a', 'b']);
        self::assertSame(
            'validation.test',
            $this->validation->getError('foo')
        );
    }

    public function testValidate() : void
    {
        self::assertTrue($this->validation->validate([]));
        self::assertSame([], $this->validation->getErrors());
        $this->validation->setRules([
            'name' => 'minLength:5',
            'email' => 'email',
        ]);
        self::assertFalse($this->validation->validate([]));
        self::assertSame(
            [
                'name' => 'The name field requires 5 or more characters in length.',
                'email' => 'The email field requires a valid email address.',
            ],
            $this->validation->getErrors()
        );
    }

    public function testValidateUnknownRule() : void
    {
        $this->validation->setRule('name', 'foo');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Validation rule 'foo' not found on field 'name'"
        );
        $this->validation->validate([]);
    }

    public function testValidateOnly() : void
    {
        self::assertTrue($this->validation->validateOnly([]));
        self::assertSame([], $this->validation->getErrors());
        $this->validation->setRules([
            'name' => 'minLength:5',
            'email' => 'email',
        ]);
        self::assertTrue($this->validation->validateOnly([]));
        self::assertSame([], $this->validation->getErrors());
        self::assertFalse($this->validation->validateOnly([
            'name' => 'foo',
            'email' => 'email',
        ]));
        self::assertSame(
            [
                'name' => 'The name field requires 5 or more characters in length.',
                'email' => 'The email field requires a valid email address.',
            ],
            $this->validation->getErrors()
        );
    }

    public function testError() : void
    {
        $this->validation->setRules([
            'email' => 'email',
        ]);
        self::assertFalse($this->validation->validate([]));
        self::assertSame(
            [
                'email' => 'The email field requires a valid email address.',
            ],
            $this->validation->getErrors()
        );
        self::assertNull(
            $this->validation->getError('unknown')
        );
        $this->validation = new ValidationMock([Validator::class], new Language('en'));
        $this->validation->setRules([
            'email' => 'email',
        ]);
        self::assertFalse($this->validation->validate([]));
        self::assertSame(
            'The email field requires a valid email address.',
            $this->validation->getError('email')
        );
        self::assertSame(
            ['email' => 'The email field requires a valid email address.'],
            $this->validation->getErrors()
        );
        $this->validation->setLabel('email', 'E-mail');
        self::assertSame(
            'The E-mail field requires a valid email address.',
            $this->validation->getError('email')
        );
        self::assertSame(
            ['email' => 'The E-mail field requires a valid email address.'],
            $this->validation->getErrors()
        );
        self::assertNull(
            $this->validation->getError('unknown')
        );
    }

    public function testOptional() : void
    {
        $this->validation->setRule('email', 'email');
        $this->validation->setRule('other', 'email');
        $this->validation->setRule('name', 'optional|minLength:5');
        $status = $this->validation->validate([
            'email' => 'user@domain.tld',
            'other' => 'other@domain.tld',
        ]);
        self::assertTrue($status);
        self::assertNull($this->validation->getError('email'));
        self::assertNull($this->validation->getError('other'));
        self::assertNull($this->validation->getError('name'));
    }

    public function testOptionalAsLastRule() : void
    {
        $this->validation->setRule('email', 'email|optional');
        $this->validation->setRule('name', 'minLength:5|optional');
        $status = $this->validation->validate([
            'name' => 'Jon',
        ]);
        self::assertFalse($status);
        self::assertNull($this->validation->getError('email'));
        self::assertStringContainsString(
            'The name field requires 5 or more characters in length.',
            $this->validation->getError('name')
        );
    }

    public function testEqualsField() : void
    {
        $this->validation->setRule('password', 'minLength:5');
        $this->validation->setRule('confirmPassword', 'equals:password');
        $this->validation->setLabels([
            'password' => 'Password',
            'confirmPassword' => 'Confirm Password',
        ]);
        $validated = $this->validation->validate([
            'password' => '123',
            'confirmPassword' => '',
        ]);
        self::assertFalse($validated);
        self::assertSame(
            'The Confirm Password field must be equals the Password field.',
            $this->validation->getError('confirmPassword')
        );
    }

    public function testEqualsFieldWithoutLabels() : void
    {
        $this->validation->setRule('password', 'minLength:5');
        $this->validation->setRule('confirmPassword', 'equals:password');
        $validated = $this->validation->validate([
            'password' => '123',
            'confirmPassword' => '',
        ]);
        self::assertFalse($validated);
        self::assertSame(
            'The confirmPassword field must be equals the password field.',
            $this->validation->getError('confirmPassword')
        );
    }
}
