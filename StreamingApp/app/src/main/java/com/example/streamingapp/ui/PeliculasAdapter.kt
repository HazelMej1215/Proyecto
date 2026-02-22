package com.example.streamingapp.ui

import android.content.Intent
import android.net.Uri
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Button
import android.widget.ImageView
import android.widget.TextView
import android.widget.Toast
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.example.streamingapp.R
import com.example.streamingapp.network.Pelicula

class PeliculasAdapter(
    private val items: MutableList<Pelicula> = mutableListOf()
) : RecyclerView.Adapter<PeliculasAdapter.VH>() {

    fun setData(nuevas: List<Pelicula>) {
        items.clear()
        items.addAll(nuevas)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val v = LayoutInflater.from(parent.context).inflate(R.layout.item_pelicula, parent, false)
        return VH(v)
    }

    override fun onBindViewHolder(holder: VH, position: Int) = holder.bind(items[position])

    override fun getItemCount(): Int = items.size

    class VH(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val imgPoster = itemView.findViewById<ImageView>(R.id.imgPoster)
        private val tvNombre = itemView.findViewById<TextView>(R.id.tvNombre)
        private val tvGenero = itemView.findViewById<TextView>(R.id.tvGenero)
        private val tvDesc = itemView.findViewById<TextView>(R.id.tvDesc)
        private val btnVer = itemView.findViewById<Button>(R.id.btnVer)

        fun bind(p: Pelicula) {
            tvNombre.text = p.nombre ?: ""
            tvGenero.text = p.genero ?: ""
            tvDesc.text = p.descripcion ?: ""

            val urlImg = (p.rutaImagen ?: "").trim()

            Glide.with(itemView)
                .load(urlImg)
                .placeholder(R.drawable.placeholder)
                .error(R.drawable.no_image)
                .into(imgPoster)

            btnVer.setOnClickListener {
                val url = (p.urlTrailer ?: "").trim()
                if (url.isBlank()) {
                    Toast.makeText(itemView.context, "No hay trailer", Toast.LENGTH_SHORT).show()
                    return@setOnClickListener
                }
                itemView.context.startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
            }
        }
    }
}