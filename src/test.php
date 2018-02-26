<?php

class Foo
{
    public function foo()
    {
        return function ($x) {
            for (;;) {
                echo $x;
            }
            foreach ($x as $y) {
                echo 'abc';
            }

            if (true) {
                echo 'foo';
            } elseif (false) {
                echo 'bar';
            } else {
                echo 'baz';
            }

            switch (true) {
            }

            try {
                do {
                    echo 'foo';
                } while (true);
            } catch (\Exception $ex) {
                throw $ex;
            } finally {
                echo 'foo';
            }
        };
    }
}
