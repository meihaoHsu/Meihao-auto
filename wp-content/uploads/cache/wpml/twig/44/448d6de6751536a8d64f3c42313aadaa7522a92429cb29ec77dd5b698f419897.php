<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* /setup/header.twig */
class __TwigTemplate_3433f0b2ffa39936c591f4e3a9a71e576afbbaf948db3cd5ca975c477290f7f3 extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<!DOCTYPE html>
<html xmlns=\"http://www.w3.org/1999/xhtml\" ";
        // line 2
        echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('language_attributes')->getCallable(), []), "html", null, true);
        echo ">
<head>
    <meta name=\"viewport\" content=\"width=device-width\"/>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
    <title>";
        // line 6
        echo \WPML\Core\twig_escape_filter($this->env, ($context["title"] ?? null), "html", null, true);
        echo "</title>
    ";
        // line 7
        echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('wp_print_scripts')->getCallable(), ["wcml-setup"]), "html", null, true);
        echo "
    ";
        // line 8
        echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('wp_do_action')->getCallable(), ["admin_print_styles"]), "html", null, true);
        echo "
    ";
        // line 9
        echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('wp_do_action')->getCallable(), ["admin_head"]), "html", null, true);
        echo "
</head>
<body class=\"wcml-setup wp-core-ui\">
<div class=\"wcml-setup__wrapper\">
<h1 id=\"wcml-logo\"><img
                src=\"";
        // line 14
        echo \WPML\Core\twig_escape_filter($this->env, ($context["WCML_PLUGIN_URL"] ?? null), "html", null, true);
        echo "/res/images/wcml-logo.png\"
                alt=\"WooCommerce Multilingual\"/></h1>

";
        // line 17
        if (($context["has_handler"] ?? null)) {
            // line 18
            echo "<form class=\"wcml-setup-form\" method=\"post\">
    <input type=\"hidden\" name=\"nonce\" value=\"";
            // line 19
            echo \WPML\Core\twig_escape_filter($this->env, ($context["nonce"] ?? null), "html", null, true);
            echo "\"/>
    <input type=\"hidden\" name=\"handle_step\" value=\"";
            // line 20
            echo \WPML\Core\twig_escape_filter($this->env, ($context["step"] ?? null), "html", null, true);
            echo "\"/>
";
        }
    }

    public function getTemplateName()
    {
        return "/setup/header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  77 => 20,  73 => 19,  70 => 18,  68 => 17,  62 => 14,  54 => 9,  50 => 8,  46 => 7,  42 => 6,  35 => 2,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html xmlns=\"http://www.w3.org/1999/xhtml\" {{ language_attributes() }}>
<head>
    <meta name=\"viewport\" content=\"width=device-width\"/>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
    <title>{{ title }}</title>
    {{ wp_print_scripts( 'wcml-setup' ) }}
    {{ wp_do_action( 'admin_print_styles' ) }}
    {{ wp_do_action( 'admin_head' ) }}
</head>
<body class=\"wcml-setup wp-core-ui\">
<div class=\"wcml-setup__wrapper\">
<h1 id=\"wcml-logo\"><img
                src=\"{{ WCML_PLUGIN_URL }}/res/images/wcml-logo.png\"
                alt=\"WooCommerce Multilingual\"/></h1>

{% if has_handler %}
<form class=\"wcml-setup-form\" method=\"post\">
    <input type=\"hidden\" name=\"nonce\" value=\"{{ nonce }}\"/>
    <input type=\"hidden\" name=\"handle_step\" value=\"{{ step }}\"/>
{% endif %}
", "/setup/header.twig", "/var/www/bike.meihao.shopping/htdocs/wp-content/plugins/woocommerce-multilingual/templates/setup/header.twig");
    }
}
