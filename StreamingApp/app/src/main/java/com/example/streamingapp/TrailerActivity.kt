package com.example.streamingapp

import android.os.Bundle
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.appcompat.app.AppCompatActivity

class TrailerActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_trailer)

        val url = intent.getStringExtra("url") ?: ""
        val web = findViewById<WebView>(R.id.webTrailer)
        web.settings.javaScriptEnabled = true
        web.webViewClient = WebViewClient()
        web.loadUrl(url)
    }
}